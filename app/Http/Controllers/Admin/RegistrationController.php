<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EmployeeRegistrationAcceptedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->get('status', 'pending');
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

        if ($search !== '') {
            $needle = mb_strtolower($search, 'UTF-8');
            $employees = $employees->filter(function ($u) use ($needle) {
                $name = mb_strtolower((string) ($u->name ?? ''), 'UTF-8');
                $email = mb_strtolower((string) ($u->email ?? ''), 'UTF-8');
                $matricule = mb_strtolower((string) ($u->matricule ?? ''), 'UTF-8');
                return str_contains($name, $needle) || str_contains($email, $needle) || str_contains($matricule, $needle);
            })->values();
        }

        $allEmployees = User::query()->where('role', 'employee')->get();

        $stats = [
            'total' => $allEmployees->count(),
            'pending' => $allEmployees->filter(fn ($u) => !$this->isEmployeeAccepted($u))->count(),
            'accepted' => $allEmployees->filter(fn ($u) => $this->isEmployeeAccepted($u))->count(),
        ];

        return view('admin.registrations.index', compact('employees', 'status', 'search', 'stats'));
    }

    public function approve(string $id)
    {
        $employee = User::query()
            ->where('role', 'employee')
            ->findOrFail($id);

        if ($this->isEmployeeAccepted($employee)) {
            return back()->with('success', 'Employe deja valide.');
        }

        $employee->update([
            'isEmployeeAccepted' => true,
        ]);

        $mailer = (string) config('mail.default', 'log');
        if (in_array($mailer, ['log', 'array'], true)) {
            Log::warning('Employee accepted but mailer is not configured for real delivery.', [
                'employee_id' => (string) $employee->id,
                'employee_email' => (string) $employee->email,
                'mailer' => $mailer,
            ]);

            return back()->with(
                'warning',
                'Employe valide, mais email non envoye: configurez MAIL_MAILER=smtp dans .env.'
            );
        }

        try {
            Mail::to($employee->email)->send(new EmployeeRegistrationAcceptedMail($employee));
            return back()->with('success', 'Employe valide avec succes. Email envoye.');
        } catch (\Throwable $e) {
            Log::error('Failed to send registration accepted email.', [
                'employee_id' => (string) $employee->id,
                'employee_email' => (string) $employee->email,
                'error' => $e->getMessage(),
            ]);

            return back()->with('warning', 'Employe valide avec succes, mais email non envoye.');
        }
    }

    public function reject(string $id)
    {
        $employee = User::query()
            ->where('role', 'employee')
            ->findOrFail($id);

        if (!empty($employee->profilePicturePath)) {
            Storage::disk('public')->delete($employee->profilePicturePath);
        }

        $employee->delete();

        return back()->with('success', 'Inscription refusee et supprimee.');
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
