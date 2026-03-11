<?php

namespace App\Services;

use App\Models\SalaireCollectives;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class SalaireCollectivesService
{
    public function create(array $data): SalaireCollectives
    {
        // ✅ Extract attachmentIds
        $attachmentIds = $data['attachmentIds'] ?? [];
        unset($data['attachmentIds']);

        // ✅ Create demande first
        $demande = SalaireCollectives::create($data);

        // ✅ Link attachments
        if (!empty($attachmentIds)) {
            Attachment::whereIn('_id', $attachmentIds)->update([
                'demandeId' => (string) $demande->_id
            ]);

            $demande->update(['attachments' => $attachmentIds]);
        }

        return $demande->fresh(['attachments']);
    }

    public function paginate(int $perPage = 15)
    {
        return SalaireCollectives::with(['employee', 'reviewer', 'attachments'])
            ->orderBy('submittedAt', 'desc')
            ->paginate($perPage);
    }

    public function paginateByEmployee(string $employeeId, int $perPage = 15)
    {
        return SalaireCollectives::where('employeeId', $employeeId)
            ->with(['attachments'])
            ->orderBy('submittedAt', 'desc')
            ->paginate($perPage);
    }

    public function findOrFail(string $id): SalaireCollectives
    {
        return SalaireCollectives::with(['employee', 'reviewer', 'attachments'])
            ->findOrFail($id);
    }

    public function update(string $id, array $data): SalaireCollectives
    {
        $demande = $this->findOrFail($id);
        $demande->update($data);
        return $demande->fresh(['attachments']);
    }

    public function delete(string $id): void
    {
        $demande = $this->findOrFail($id);
        
        // Delete file attachments
        $attachments = Attachment::where('demandeId', (string) $demande->_id)->get();
        foreach ($attachments as $attachment) {
            if ($attachment->filepath) {
                Storage::disk('public')->delete($attachment->filepath);
            }
            $attachment->delete();
        }
        
        $demande->delete();
    }
}
