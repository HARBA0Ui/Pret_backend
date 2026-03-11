<?php

namespace App\Services;

use App\Models\Mensualite;
use App\Models\Pret;
use Illuminate\Http\Request;

class DemandePlafondService
{
    // constants
    public const PLAFOND_SOCIETE = 10000.0;
    public const MAX_MONTHS = 60;
    public const YEARLY_RATE = 0.04; // 4%

    public function monthlyRate(): float
    {
        return self::YEARLY_RATE / 12.0;
    }

    // Amortized monthly payment: M = P*r / (1 - (1+r)^-n)
    public function monthlyPayment(float $principal, int $months): float
    {
        if ($principal <= 0 || $months <= 0) return 0.0;

        $r = $this->monthlyRate();

        // edge: if r is ~0
        if (abs($r) < 1e-12) {
            return $principal / $months;
        }

        $den = 1.0 - pow(1.0 + $r, -$months);
        if ($den <= 0) return INF;

        return ($principal * $r) / $den;
    }

    // Given payment cap and months, compute max principal affordable
    // P = M * (1 - (1+r)^-n) / r
    public function maxPrincipalFromMonthlyCap(float $monthlyCap, int $months): float
    {
        if ($monthlyCap <= 0 || $months <= 0) return 0.0;

        $r = $this->monthlyRate();
        if (abs($r) < 1e-12) {
            return $monthlyCap * $months;
        }

        $factor = (1.0 - pow(1.0 + $r, -$months)) / $r;
        if ($factor <= 0) return 0.0;

        return $monthlyCap * $factor;
    }

    // Min months needed so that payment <= cap (<=60)
    public function minMonthsForAmount(float $principal, float $monthlyCap): ?int
    {
        if ($principal <= 0) return 1;
        if ($monthlyCap <= 0) return null;

        $r = $this->monthlyRate();
        if (abs($r) < 1e-12) {
            $n = (int) ceil($principal / $monthlyCap);
            return ($n <= self::MAX_MONTHS) ? max(1, $n) : null;
        }

        // Need: M(P,n) <= cap
        // P*r/(1-(1+r)^-n) <= cap
        // 1-(1+r)^-n >= P*r/cap
        // (1+r)^-n <= 1 - P*r/cap
        $x = 1.0 - ($principal * $r / $monthlyCap);
        if ($x <= 0) return null; // impossible even with infinite months

        $nReal = -log($x) / log(1.0 + $r);
        $n = (int) ceil($nReal);

        if ($n < 1) $n = 1;
        if ($n > self::MAX_MONTHS) return null;
        return $n;
    }

    public function sumExistingMensualitesForActiveLoans(string $employeeId): float
    {
        // active/approved loan ids
        $activePretIds = Pret::query()
            ->where('employeeId', $employeeId)
            ->whereIn('status', ['active', 'approved'])
            ->pluck('_id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        if (empty($activePretIds)) return 0.0;

        // mensualites pending for those loans
        $sum = Mensualite::query()
            ->where('employeeId', $employeeId)
            ->whereIn('pretId', $activePretIds)
            ->whereIn('status', ['pending', 'PENDING'])
            ->sum('amount');

        return (float) $sum;
    }

    public function compute(Request $request, float $cnssSum, int $months, ?float $amount = null): array
    {
        $employee = $request->user();
        $salary = (float) ($employee->salaire ?? 0);

        $months = max(1, min(self::MAX_MONTHS, $months));

        $plafondSociete = self::PLAFOND_SOCIETE;
        $plafondCnss = max(0.0, $cnssSum / 6.0);

        $sumExisting = $this->sumExistingMensualitesForActiveLoans((string) $employee->id);

        $mensualite40pct = 0.40 * max(0.0, $salary);
        $monthlyCapacity = $mensualite40pct - $sumExisting;
        if ($monthlyCapacity < 0) $monthlyCapacity = 0.0;

        $maxByMensualite = $this->maxPrincipalFromMonthlyCap($monthlyCapacity, $months);

        $maxAmount = min($plafondSociete, $plafondCnss, $maxByMensualite);
        if ($maxAmount < 0) $maxAmount = 0.0;

        $minMonthsForAmount = null;
        $monthlyPayment = null;

        if ($amount !== null && $amount > 0) {
            // if amount exceeds hard caps, still return minMonthsForAmount=null (force reduce amount)
            $hardMax = min($plafondSociete, $plafondCnss);
            if ($amount <= $hardMax && $monthlyCapacity > 0) {
                $minMonthsForAmount = $this->minMonthsForAmount($amount, $monthlyCapacity);
                $monthlyPayment = ($minMonthsForAmount !== null)
                    ? $this->monthlyPayment($amount, $minMonthsForAmount)
                    : null;
            }
        }

        return [
            'plafondSociete' => round($plafondSociete, 2),
            'plafondCnss' => round($plafondCnss, 2),

            'salaryMonthly' => round($salary, 2),
            'mensualite40pct' => round($mensualite40pct, 2),

            'sumExistingMensualites' => round($sumExisting, 2),
            'monthlyCapacity' => round($monthlyCapacity, 2),

            'months' => $months,
            'yearlyRate' => self::YEARLY_RATE,
            'monthlyRate' => round($this->monthlyRate(), 8),

            'maxByMensualite' => round($maxByMensualite, 2),
            'maxAmount' => round($maxAmount, 2),

            // extra for UX
            'minMonthsForAmount' => $minMonthsForAmount,
            'monthlyPaymentForMinMonths' => $monthlyPayment !== null ? round($monthlyPayment, 2) : null,
        ];
    }

    public function validateDemandeRequest(Request $request, array $data): void
    {
        // used server-side in store() for non-remboursement demandes

        $cnssSum = (float) ($data['cnssSum4Sem'] ?? 0);
        $months = (int) ($data['dureeMonths'] ?? 0);
        $amount = (float) ($data['amountRequested'] ?? 0);

        if ($months < 1 || $months > self::MAX_MONTHS) {
            abort(response()->json(['message' => 'Durée invalide (max 60 mois).'], 422));
        }

        if ($cnssSum <= 0) {
            abort(response()->json(['message' => 'Somme CNSS invalide.'], 422));
        }

        $cnssIds = $data['cnssAttachmentIds'] ?? [];
        if (!is_array($cnssIds) || count($cnssIds) !== 4) {
            abort(response()->json(['message' => 'Veuillez fournir exactement 4 pièces CNSS.'], 422));
        }

        $limits = $this->compute($request, $cnssSum, $months, $amount);

        if ($limits['maxAmount'] <= 0) {
            abort(response()->json(['message' => "Capacité mensuelle insuffisante (40% - échéances actives)."], 422));
        }

        if ($amount > $limits['maxAmount']) {
            abort(response()->json(['message' => "Montant dépasse le plafond autorisé ({$limits['maxAmount']} TND) pour {$months} mois."], 422));
        }

        // also ensure user can afford at all for that amount
        if ($limits['minMonthsForAmount'] === null) {
            abort(response()->json(['message' => "Ce montant n'est pas finançable avec votre capacité mensuelle (max 60 mois)."], 422));
        }

        if ($months < (int) $limits['minMonthsForAmount']) {
            abort(response()->json(['message' => "Durée insuffisante. Durée minimale: {$limits['minMonthsForAmount']} mois."], 422));
        }
    }
}
