<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Mensualite;
use App\Models\Pret;
use App\Models\RemboursementAnticipe;
use Illuminate\Support\Facades\Storage;

class RemboursementAnticipeService
{
    private const PAID_STATUSES = ['paid', 'paye', 'payé'];

    public function create(array $data): RemboursementAnticipe
    {
        $attachmentIds = $data['attachmentIds'] ?? [];
        unset($data['attachmentIds']);

        $demande = RemboursementAnticipe::create($data);

        if (!empty($attachmentIds)) {
            Attachment::whereIn('_id', $attachmentIds)->update([
                'demandeId' => (string) $demande->_id,
            ]);

            $demande->update(['attachments' => $attachmentIds]);
        }

        return $demande->fresh(['attachments']);
    }

    public function paginate(int $perPage = 15)
    {
        return RemboursementAnticipe::with(['employee', 'reviewer', 'attachments'])
            ->orderBy('submittedAt', 'desc')
            ->paginate($perPage);
    }

    public function paginateByEmployee(string $employeeId, int $perPage = 15)
    {
        return RemboursementAnticipe::where('employeeId', $employeeId)
            ->with(['attachments'])
            ->orderBy('submittedAt', 'desc')
            ->paginate($perPage);
    }

    public function findOrFail(string $id): RemboursementAnticipe
    {
        return RemboursementAnticipe::with(['employee', 'reviewer', 'attachments'])
            ->findOrFail($id);
    }

    public function update(string $id, array $data): RemboursementAnticipe
    {
        $demande = $this->findOrFail($id);
        $demande->update($data);

        return $demande->fresh(['attachments']);
    }

    public function delete(string $id): void
    {
        $demande = $this->findOrFail($id);

        $attachments = Attachment::where('demandeId', (string) $demande->_id)->get();
        foreach ($attachments as $attachment) {
            if ($attachment->filepath) {
                Storage::disk('public')->delete($attachment->filepath);
            }

            $attachment->delete();
        }

        $demande->delete();
    }

    public function approve(string $id, string $adminId): RemboursementAnticipe
    {
        $demande = $this->findOrFail($id);

        if ($demande->status !== 'En attente') {
            throw new \RuntimeException('Demande déjà traitée.');
        }

        $demande->update([
            'status' => 'Approuvée',
            'reviewedAt' => now(),
            'decisionAt' => now(),
            'reviewByAdminId' => (string) $adminId,
        ]);

        return $demande->fresh();
    }

    public function approveAndApplyToPret(
        RemboursementAnticipe $demande,
        string $adminId,
        ?float $approvedAmount = null
    ): RemboursementAnticipe {
        if ((string) ($demande->status ?? '') !== 'En attente') {
            throw new \RuntimeException('Demande déjà traitée.');
        }

        $loanId = trim((string) ($demande->loanIdToRepay ?? ''));
        if ($loanId === '') {
            throw new \RuntimeException('Prêt cible manquant.');
        }

        $employeeId = (string) ($demande->employeeId ?? '');
        $pret = Pret::query()
            ->where('employeeId', $employeeId)
            ->find($loanId);

        if (!$pret) {
            throw new \RuntimeException('Prêt introuvable pour cet employé.');
        }

        if (!$this->isPretEligibleForEarlyRepayment((string) ($pret->status ?? ''))) {
            throw new \RuntimeException('Ce prêt ne peut pas être remboursé de façon anticipée.');
        }

        $requestedAmount = (float) ($demande->earlyRepaymentAmount ?? $demande->amountRequested ?? 0);
        $amountToApply = $approvedAmount !== null ? (float) $approvedAmount : $requestedAmount;
        $amountToApply = round($amountToApply, 2);

        if ($amountToApply <= 0) {
            throw new \RuntimeException('Montant de remboursement anticipé invalide.');
        }

        if ($requestedAmount > 0 && $amountToApply > $requestedAmount + 0.0001) {
            throw new \RuntimeException('Le montant approuvé ne peut pas dépasser le montant demandé.');
        }

        $remainingBefore = $this->computeRemainingBalanceForPret($pret);
        if ($remainingBefore <= 0) {
            throw new \RuntimeException('Ce prêt n\'a plus de solde restant.');
        }

        if ($amountToApply > $remainingBefore + 0.0001) {
            throw new \RuntimeException(
                'Le montant approuvé dépasse le solde restant (' . number_format($remainingBefore, 2, '.', '') . ' TND).'
            );
        }

        $this->applyRepaymentOnMensualites($pret, $amountToApply);

        $remainingAfter = max(0.0, round($remainingBefore - $amountToApply, 2));

        $pretUpdates = [
            'remainingBalance' => $remainingAfter,
            'updatedAt' => now(),
        ];

        if ($remainingAfter <= 0.0001) {
            $pretUpdates['status'] = 'completed';
            $this->markAllPendingMensualitesAsPaid($pret);
        }

        $pret->update($pretUpdates);

        $demande->update([
            'status' => 'Approuvée',
            'approvedAmount' => $amountToApply,
            'reviewedAt' => now(),
            'decisionAt' => now(),
            'reviewByAdminId' => (string) $adminId,
        ]);

        return $demande->fresh(['employee', 'reviewer', 'attachments']);
    }

    public function computeRemainingBalanceForPret(Pret $pret): float
    {
        if ($pret->remainingBalance !== null) {
            return max(0.0, round((float) $pret->remainingBalance, 2));
        }

        $pretId = (string) ($pret->id ?? $pret->_id ?? '');
        if ($pretId === '') {
            $totalToRepay = (float) ($pret->totalToRepay ?? 0);
            if ($totalToRepay > 0) {
                return max(0.0, round($totalToRepay, 2));
            }

            return max(0.0, round((float) ($pret->amount ?? 0), 2));
        }

        $mensualites = Mensualite::query()
            ->where('pretId', $pretId)
            ->get();

        if ($mensualites->isNotEmpty()) {
            $total = (float) $mensualites->sum(fn ($m) => (float) ($m->amount ?? 0));
            $paid = (float) $mensualites
                ->filter(fn ($m) => $this->isPaidStatus((string) ($m->status ?? '')))
                ->sum(fn ($m) => (float) ($m->amount ?? 0));

            return max(0.0, round($total - $paid, 2));
        }

        $totalToRepay = (float) ($pret->totalToRepay ?? 0);
        if ($totalToRepay > 0) {
            return max(0.0, round($totalToRepay, 2));
        }

        return max(0.0, round((float) ($pret->amount ?? 0), 2));
    }

    private function applyRepaymentOnMensualites(Pret $pret, float $amountToApply): void
    {
        if ($amountToApply <= 0) {
            return;
        }

        $pretId = (string) ($pret->id ?? $pret->_id ?? '');
        if ($pretId === '') {
            return;
        }

        $remainingToApply = round($amountToApply, 2);

        $mensualites = Mensualite::query()
            ->where('pretId', $pretId)
            ->orderBy('dueDate', 'asc')
            ->orderBy('createdAt', 'asc')
            ->get();

        foreach ($mensualites as $mensualite) {
            if ($remainingToApply <= 0.0001) {
                break;
            }

            if ($this->isPaidStatus((string) ($mensualite->status ?? ''))) {
                continue;
            }

            $installmentAmount = round((float) ($mensualite->amount ?? 0), 2);
            if ($installmentAmount <= 0) {
                continue;
            }

            if ($remainingToApply + 0.0001 >= $installmentAmount) {
                $mensualite->update([
                    'status' => 'Payé',
                    'paidAmount' => $installmentAmount,
                    'paidDate' => now(),
                    'updatedAt' => now(),
                ]);

                $remainingToApply = round($remainingToApply - $installmentAmount, 2);
                continue;
            }

            $paidPart = round($remainingToApply, 2);
            $unpaidPart = round($installmentAmount - $paidPart, 2);

            if ($paidPart <= 0 || $unpaidPart <= 0) {
                continue;
            }

            $originalPaymentNumber = $mensualite->paymentNumber ?? null;
            $originalDueDate = $mensualite->dueDate ?? null;

            $mensualite->update([
                'amount' => $paidPart,
                'status' => 'Payé',
                'paidAmount' => $paidPart,
                'paidDate' => now(),
                'updatedAt' => now(),
            ]);

            Mensualite::create([
                'pretId' => $pretId,
                'employeeId' => (string) ($pret->employeeId ?? ''),
                'paymentNumber' => $originalPaymentNumber,
                'amount' => $unpaidPart,
                'dueDate' => $originalDueDate,
                'status' => 'En attente',
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);

            $remainingToApply = 0.0;
        }
    }

    private function markAllPendingMensualitesAsPaid(Pret $pret): void
    {
        $pretId = (string) ($pret->id ?? $pret->_id ?? '');
        if ($pretId === '') {
            return;
        }

        $mensualites = Mensualite::query()
            ->where('pretId', $pretId)
            ->get();

        foreach ($mensualites as $mensualite) {
            if ($this->isPaidStatus((string) ($mensualite->status ?? ''))) {
                continue;
            }

            $amount = round((float) ($mensualite->amount ?? 0), 2);

            $mensualite->update([
                'status' => 'Payé',
                'paidAmount' => $amount,
                'paidDate' => now(),
                'updatedAt' => now(),
            ]);
        }
    }

    private function isPaidStatus(string $status): bool
    {
        $normalized = mb_strtolower(trim($status), 'UTF-8');
        return in_array($normalized, self::PAID_STATUSES, true);
    }

    private function isPretEligibleForEarlyRepayment(string $status): bool
    {
        $normalized = mb_strtolower(trim($status), 'UTF-8');

        return in_array($normalized, [
            'active',
            'actif',
            'approved',
            'approuvé',
            'approuve',
        ], true);
    }
}
