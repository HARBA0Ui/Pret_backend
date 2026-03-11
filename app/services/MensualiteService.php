<?php

namespace App\Services;

use App\Models\Mensualite;
use App\Models\Pret;
use Carbon\Carbon;

class MensualiteService
{
    public function paginate(int $perPage = 15)
    {
        return Mensualite::latest()->paginate($perPage);
    }

    public function paginateByEmployee(string $employeeId, int $perPage = 15)
    {
        return Mensualite::where('employeeId', $employeeId)
            ->latest()
            ->paginate($perPage);
    }

    public function nextDueByEmployee(string $employeeId)
    {
        $today = Carbon::today();

        return Mensualite::where('employeeId', $employeeId)
            ->whereNotNull('dueDate')
            ->where('dueDate', '>=', $today)
            ->orderBy('dueDate', 'asc')
            ->first();
    }

    public function create(array $data): Mensualite
    {
        return Mensualite::create($data);
    }

    public function findOrFail(string $id): Mensualite
    {
        return Mensualite::findOrFail($id);
    }

    public function update(string $id, array $data): Mensualite
    {
        $doc = $this->findOrFail($id);
        $doc->update($data);
        return $doc->refresh();
    }

    public function delete(string $id): void
    {
        $this->findOrFail($id)->delete();
    }

    /**
     * ✅ Génère automatiquement les mensualités d'un prêt
     * - Idempotent
     * - Appelé UNIQUEMENT à l'approbation admin
     */
    public function createScheduleForPret(Pret $pret, ?Carbon $firstDueDate = null): int
    {
        $months = (int) ($pret->dureeMonths ?? 0);
        if ($months <= 0) return 0;

        if (Mensualite::where('pretId', (string) $pret->id)->exists()) {
            return 0;
        }

        $first = $firstDueDate
            ? $firstDueDate->copy()
            : Carbon::today()->addMonthNoOverflow();

        $mensualite = $this->resolveMonthlyAmount($pret);
        $created = 0;

        for ($i = 0; $i < $months; $i++) {
            Mensualite::create([
                'pretId' => (string) $pret->id,
                'employeeId' => (string) $pret->employeeId,
                'amount' => round($mensualite, 2),
                'dueDate' => $first->copy()->addMonthsNoOverflow($i)->toDateString(),
                'status' => 'En attente',
                'submittedAt' => now(),
            ]);
            $created++;
        }

        return $created;
    }

    private function resolveMonthlyAmount(Pret $pret): float
    {
        $p = (float) $pret->amount;
        $n = (int) $pret->dureeMonths;

        if (!empty($pret->mensualite)) {
            return (float) $pret->mensualite;
        }

        $annualRate = (float) ($pret->interestRate ?? 0.04);
        $r = $annualRate / 12;

        if ($r <= 0) return $p / $n;

        return ($p * $r) / (1 - pow(1 + $r, -$n));
    }
}
