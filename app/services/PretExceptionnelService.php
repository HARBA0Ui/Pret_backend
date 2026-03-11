<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\PretExceptionnel;
use Illuminate\Support\Facades\Storage;

class PretExceptionnelService
{
    public function create(array $data): PretExceptionnel
    {
        $attachmentIds = $data['attachmentIds'] ?? [];
        unset($data['attachmentIds']);

        $demande = PretExceptionnel::create($data);

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
        return PretExceptionnel::with(['employee', 'reviewer', 'attachments'])
            ->orderBy('submittedAt', 'desc')
            ->paginate($perPage);
    }

    public function paginateByEmployee(string $employeeId, int $perPage = 15)
    {
        return PretExceptionnel::where('employeeId', $employeeId)
            ->with(['attachments'])
            ->orderBy('submittedAt', 'desc')
            ->paginate($perPage);
    }

    public function findOrFail(string $id): PretExceptionnel
    {
        return PretExceptionnel::with(['employee', 'reviewer', 'attachments'])
            ->findOrFail($id);
    }

    public function update(string $id, array $data): PretExceptionnel
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

    public function approve(string $id, ?float $approvedAmount, string $adminId): PretExceptionnel
    {
        $demande = $this->findOrFail($id);

        if ($demande->status !== 'En attente') {
            throw new \RuntimeException('Demande déjà traitée.');
        }

        $finalAmount = ($approvedAmount !== null && $approvedAmount > 0)
            ? (float) $approvedAmount
            : (float) $demande->amountRequested;

        $demande->update([
            'status' => 'Approuvée',
            'approvedAmount' => $finalAmount,
            'reviewedAt' => now(),
            'decisionAt' => now(),
            'reviewByAdminId' => (string) $adminId,
        ]);

        return $demande->fresh();
    }
}
