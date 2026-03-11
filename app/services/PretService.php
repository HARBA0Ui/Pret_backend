<?php

namespace App\Services;

use App\Models\Mensualite;
use App\Models\Pret;
use Carbon\Carbon;

class PretService
{
    public const INTERET_FIXE = 4.0; // ✅ Tunisair (4%)

    public function paginate(int $perPage = 15)
    {
        return Pret::with(['employee', 'mensualites'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function paginateByEmployee(string $employeeId, int $perPage = 15)
    {
        return Pret::where('employeeId', $employeeId)
            ->with(['mensualites'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findOrFail(string $id): Pret
    {
        return Pret::with(['employee', 'mensualites'])->findOrFail($id);
    }

    public function create(array $data): Pret
    {
        // (si tu crées des prêts manuellement via API)
        $pret = Pret::create($data);
        return $pret->fresh(['employee', 'mensualites']);
    }

    public function update(string $id, array $data): Pret
    {
        $pret = $this->findOrFail($id);
        $pret->update($data);
        return $pret->fresh(['employee', 'mensualites']);
    }

    public function delete(string $id): void
    {
        $pret = $this->findOrFail($id);

        // supprimer mensualités associées
        Mensualite::where('pretId', (string) $pret->_id)->delete();

        $pret->delete();
    }

    /**
     * ✅ Création automatique du prêt après approbation d'une demande
     */
    public function createFromApprovedDemande(
        string $demandeId,
        string $employeeId,
        float $amount,
        int $dureeMonths,
        ?Carbon $startDate = null
    ): Pret {
        if ($dureeMonths <= 0) {
            throw new \InvalidArgumentException("La durée (mois) doit être > 0.");
        }

        $startDate = $startDate ?: now();

        // Intérêt fixe 4% (flat)
        $interestRate = (float) self::INTERET_FIXE;

        // total à rembourser
        $totalToRepay = round($amount * (1 + ($interestRate / 100)), 2);

        $pret = Pret::create([
            'demandeId' => $demandeId,
            'employeeId' => $employeeId,
            'interestRate' => $interestRate,
            'startDate' => $startDate,
            'durationMonths' => (int) $dureeMonths,     // ✅ on garde ton champ existant Pret.php
            'amount' => (float) $amount,
            'status' => 'active',
            'remainingBalance' => (float) $totalToRepay,
            'totalPaid' => 0,
        ]);

        $this->generateMensualites($pret);

        return $pret->fresh(['employee', 'mensualites']);
    }

    /**
     * ✅ Génère exactement N mensualités
     */
    public function generateMensualites(Pret $pret): void
    {
        $months = (int) ($pret->durationMonths ?? 0);
        if ($months <= 0) return;

        // éviter doublons
        Mensualite::where('pretId', (string) $pret->_id)->delete();

        $total = (float) ($pret->remainingBalance ?? $pret->amount ?? 0);
        if ($total <= 0) return;

        $base = round($total / $months, 2);
        $last = round($total - ($base * ($months - 1)), 2);

        for ($i = 1; $i <= $months; $i++) {
            $amount = ($i === $months) ? $last : $base;

            Mensualite::create([
                'pretId' => (string) $pret->_id,
                'employeeId' => (string) $pret->employeeId,
                'amount' => (float) $amount,
                'dueDate' => Carbon::parse($pret->startDate)->copy()->addMonths($i)->startOfDay(),
                'status' => 'pending',
                'paidAmount' => 0,
                'paidDate' => null,
            ]);
        }
    }
}
