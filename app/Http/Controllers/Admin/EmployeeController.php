<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AideSociale;
use App\Models\Attachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\DonsScolaire;
use App\Models\Mensualite;
use App\Models\Pret;
use App\Models\PretExceptionnel;
use App\Models\PretHajj;
use App\Models\RemboursementAnticipe;
use App\Models\SalaireCollectives;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    private const DIRECTION_OPTIONS = [
        'commercial' => 'Commercial',
        'technique' => 'Technique',
        'financiere' => 'Financiere',
        'rh' => 'RH',
        'operations' => 'Operations',
    ];

    public function index(Request $request)
    {
        $status = (string) $request->get('status', 'all');
        $direction = (string) $request->get('direction', 'all');
        $search = trim((string) $request->get('search', ''));

        $employees = User::query()
            ->where('role', 'employee')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($status === 'pending') {
            $employees = $employees->filter(fn ($u) => !$this->isEmployeeAccepted($u))->values();
        } elseif ($status === 'accepted') {
            $employees = $employees->filter(fn ($u) => $this->isEmployeeAccepted($u))->values();
        }

        if ($direction !== 'all') {
            $employees = $employees
                ->filter(fn ($u) => (string) ($u->direction ?? '') === $direction)
                ->values();
        }

        if ($search !== '') {
            $needle = mb_strtolower($search, 'UTF-8');
            $employees = $employees->filter(function ($u) use ($needle) {
                $name = mb_strtolower((string) ($u->name ?? ''), 'UTF-8');
                $email = mb_strtolower((string) ($u->email ?? ''), 'UTF-8');
                $matricule = mb_strtolower((string) ($u->matricule ?? ''), 'UTF-8');

                return str_contains($name, $needle)
                    || str_contains($email, $needle)
                    || str_contains($matricule, $needle);
            })->values();
        }

        $allEmployees = User::query()->where('role', 'employee')->get();
        $stats = [
            'total' => $allEmployees->count(),
            'accepted' => $allEmployees->filter(fn ($u) => $this->isEmployeeAccepted($u))->count(),
            'pending' => $allEmployees->filter(fn ($u) => !$this->isEmployeeAccepted($u))->count(),
        ];

        $directionOptions = self::DIRECTION_OPTIONS;

        return view('admin.employees.index', compact(
            'employees',
            'status',
            'direction',
            'search',
            'stats',
            'directionOptions'
        ));
    }

    public function updateDirection(Request $request, string $id)
    {
        $data = $request->validate([
            'direction' => 'required|string|in:commercial,technique,financiere,rh,operations',
        ]);

        $employee = User::query()
            ->where('role', 'employee')
            ->findOrFail($id);

        $employee->update([
            'direction' => $data['direction'],
        ]);

        return back()->with('success', 'Direction mise a jour avec succes.');
    }

    public function destroy(string $id)
    {
        $employee = User::query()
            ->where('role', 'employee')
            ->findOrFail($id);

        $employeeId = (string) $employee->id;

        if (!empty($employee->profilePicturePath)) {
            Storage::disk('public')->delete($employee->profilePicturePath);
        }

        $this->deleteEmployeeDemandesAndAttachments($employeeId);

        Mensualite::query()->where('employeeId', $employeeId)->delete();
        Pret::query()->where('employeeId', $employeeId)->delete();

        $conversations = ChatConversation::query()
            ->where('employeeId', $employeeId)
            ->get();

        foreach ($conversations as $conversation) {
            ChatMessage::query()
                ->where('conversationId', (string) $conversation->id)
                ->delete();
        }

        ChatConversation::query()->where('employeeId', $employeeId)->delete();

        $uploadedAttachments = Attachment::query()
            ->where('uploadedBy', $employeeId)
            ->get();

        foreach ($uploadedAttachments as $attachment) {
            if (!empty($attachment->filepath)) {
                Storage::disk('public')->delete($attachment->filepath);
            }
        }

        Attachment::query()->where('uploadedBy', $employeeId)->delete();

        if (method_exists($employee, 'tokens')) {
            $employee->tokens()->delete();
        }

        $employee->delete();

        return back()->with('success', 'Employe supprime avec ses donnees associees.');
    }

    private function deleteEmployeeDemandesAndAttachments(string $employeeId): void
    {
        $demandeModels = [
            AideSociale::class,
            DonsScolaire::class,
            PretExceptionnel::class,
            PretHajj::class,
            SalaireCollectives::class,
            RemboursementAnticipe::class,
        ];

        foreach ($demandeModels as $demandeModel) {
            $demandes = $demandeModel::query()
                ->where('employeeId', $employeeId)
                ->get();

            foreach ($demandes as $demande) {
                $attachments = Attachment::query()
                    ->where('demandeId', (string) $demande->id)
                    ->get();

                foreach ($attachments as $attachment) {
                    if (!empty($attachment->filepath)) {
                        Storage::disk('public')->delete($attachment->filepath);
                    }
                }

                Attachment::query()->where('demandeId', (string) $demande->id)->delete();
                $demande->delete();
            }
        }
    }

    private function isEmployeeAccepted(User $employee): bool
    {
        $attributes = $employee->getAttributes();
        if (!array_key_exists('isEmployeeAccepted', $attributes)) {
            return true;
        }

        return (bool) ($employee->isEmployeeAccepted ?? false);
    }
}

