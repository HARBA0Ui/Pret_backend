<?php

namespace App\Services;

use App\Models\Pret;

class PretCreationService
{
    public function __construct(
        private MensualiteService $mensualiteService
    ) {}

    public function createFromDemande(object $demande, string $type = 'demande'): Pret
    {
        $existing = Pret::where('demandeType', $type)
            ->where('demandeId', (string) ($demande->id ?? ''))
            ->first();

        if ($existing) {
            $this->mensualiteService->createScheduleForPret($existing);
            return $existing;
        }

        $employeeId = (string) ($demande->employeeId ?? $demande->employee_id ?? '');
        $months = (int) ($demande->dureeMonths ?? $demande->duree_months ?? 0);
        $amount = (float) (($demande->approvedAmount ?? null) !== null
            ? $demande->approvedAmount
            : ($demande->amountRequested ?? $demande->amount ?? 0));

        if ($amount <= 0 || $months <= 0 || !$employeeId) {
            throw new \Exception("Demande invalide : employeeId / montant / durée manquants.");
        }

        $interestRate = (float) ($demande->interestRate ?? 0.04);
        $monthlyAmount = $this->resolveMonthlyAmount($amount, $months, $interestRate);
        $totalToRepay = round($monthlyAmount * $months, 2);

        $pret = Pret::create([
            'employeeId' => $employeeId,
            'demandeType' => $type,
            'demandeId' => (string) ($demande->id ?? ''),
            'amount' => $amount,
            'dureeMonths' => $months,
            'interestRate' => $interestRate,
            'startDate' => now(),
            'totalToRepay' => $totalToRepay,
            'remainingBalance' => $totalToRepay,
            'status' => 'Actif',
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $this->mensualiteService->createScheduleForPret($pret);

        return $pret;
    }

    private function resolveMonthlyAmount(float $amount, int $months, float $annualRate): float
    {
        if ($amount <= 0 || $months <= 0) {
            return 0.0;
        }

        $monthlyRate = $annualRate / 12;
        if ($monthlyRate <= 0) {
            return $amount / $months;
        }

        return ($amount * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));
    }
}
