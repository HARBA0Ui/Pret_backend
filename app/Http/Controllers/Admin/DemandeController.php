<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AideSociale;
use App\Models\DonsScolaire;
use App\Models\PretExceptionnel;
use App\Models\PretHajj;
use App\Models\RemboursementAnticipe;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DemandeController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');

        // ✅ input search (name/email) - case-insensitive
        $employeeSearch = trim((string) $request->get('employee_search', ''));

        // ✅ Dates
        $dateFrom = $request->get('date_from'); // YYYY-MM-DD
        $dateTo = $request->get('date_to');     // YYYY-MM-DD

        $demandes = collect();

        if ($type === 'all' || $type === 'aide-sociale') {
            $aides = AideSociale::with('employee')->get()->map(function ($d) {
                $d->type = 'Aide Sociale';
                $d->typeSlug = 'aide-sociale';
                return $d;
            });
            $demandes = $demandes->merge($aides);
        }

        if ($type === 'all' || $type === 'don-scolaire') {
            $dons = DonsScolaire::with('employee')->get()->map(function ($d) {
                $d->type = 'Don Scolaire';
                $d->typeSlug = 'don-scolaire';
                return $d;
            });
            $demandes = $demandes->merge($dons);
        }

        if ($type === 'all' || $type === 'pret-exceptionnel') {
            $exceptionnels = PretExceptionnel::with('employee')->get()->map(function ($d) {
                $d->type = 'Prêt Exceptionnel';
                $d->typeSlug = 'pret-exceptionnel';
                return $d;
            });
            $demandes = $demandes->merge($exceptionnels);
        }

        if ($type === 'all' || $type === 'pret-hajj') {
            $hajjs = PretHajj::with('employee')->get()->map(function ($d) {
                $d->type = 'Prêt Hajj';
                $d->typeSlug = 'pret-hajj';
                return $d;
            });
            $demandes = $demandes->merge($hajjs);
        }

        if ($type === 'all' || $type === 'remboursement') {
            $remboursements = RemboursementAnticipe::with('employee')->get()->map(function ($d) {
                $d->type = 'Remboursement Anticipé';
                $d->typeSlug = 'remboursement-anticipe';
                return $d;
            });
            $demandes = $demandes->merge($remboursements);
        }

        // ✅ Filter by status
        if ($status !== 'all') {
            $demandes = $demandes->filter(fn ($d) => $d->status === $status);
        }

        // ✅ FIX: Filter by employee search using loaded relation (works with Mongo)
        if ($employeeSearch !== '') {
            $needle = mb_strtolower($employeeSearch, 'UTF-8');

            $demandes = $demandes->filter(function ($d) use ($needle) {
                $name = mb_strtolower((string) ($d->employee->name ?? ''), 'UTF-8');
                $email = mb_strtolower((string) ($d->employee->email ?? ''), 'UTF-8');
                $matricule = mb_strtolower((string) ($d->employee->matricule ?? ''), 'UTF-8');

                return str_contains($name, $needle)
                    || str_contains($email, $needle)
                    || str_contains($matricule, $needle);
            });
        }

        // ✅ Filter by date range on submittedAt
        if ($dateFrom || $dateTo) {
            $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
            $to   = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

            $demandes = $demandes->filter(function ($d) use ($from, $to) {
                if (!$d->submittedAt) return false;

                $dt = $d->submittedAt instanceof \Carbon\Carbon
                    ? $d->submittedAt
                    : Carbon::parse($d->submittedAt);

                if ($from && $dt->lt($from)) return false;
                if ($to && $dt->gt($to)) return false;
                return true;
            });
        }

        $demandes = $demandes->sortByDesc('submittedAt')->values();

        return view('admin.demandes.index', compact(
            'demandes',
            'type',
            'status',
            'employeeSearch',
            'dateFrom',
            'dateTo'
        ));
    }

    public function show($id, Request $request)
    {
        $type = $request->get('type', 'aide-sociale');

        $demande = match ($type) {
            'aide-sociale' => AideSociale::with(['employee', 'attachments'])->findOrFail($id),
            'don-scolaire' => DonsScolaire::with(['employee', 'attachments'])->findOrFail($id),
            'pret-exceptionnel' => PretExceptionnel::with(['employee', 'attachments'])->findOrFail($id),
            'pret-hajj' => PretHajj::with(['employee', 'attachments'])->findOrFail($id),
            'remboursement', 'remboursement-anticipe' => RemboursementAnticipe::with(['employee', 'attachments'])->findOrFail($id),
            default => abort(404, 'Type de demande invalide'),
        };

        $demande->type = match ($type) {
            'aide-sociale' => 'Aide Sociale',
            'don-scolaire' => 'Don Scolaire',
            'pret-exceptionnel' => 'Prêt Exceptionnel',
            'pret-hajj' => 'Prêt Hajj',
            'remboursement', 'remboursement-anticipe' => 'Remboursement Anticipé',
            default => 'Inconnu',
        };

        $demande->typeSlug = match ($type) {
            'aide-sociale' => 'aide-sociale',
            'don-scolaire' => 'don-scolaire',
            'pret-exceptionnel' => 'pret-exceptionnel',
            'pret-hajj' => 'pret-hajj',
            'remboursement', 'remboursement-anticipe' => 'remboursement-anticipe',
            default => 'unknown',
        };

        return view('admin.demandes.show', compact('demande'));
    }
}
