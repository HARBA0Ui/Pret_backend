<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pret;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PretController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        // ✅ input search (name/email)
        $employeeSearch = trim((string) $request->get('employee_search', ''));

        // ✅ Dates
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Pret::with('employee');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // ✅ Date range filter on created_at
        if ($dateFrom || $dateTo) {
            $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::minValue();
            $to   = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::maxValue();

            $query->whereBetween('created_at', [$from, $to]);
        }

        $prets = $query->orderBy('created_at', 'desc')->get();

        // ✅ FIX: Filter by employee search using loaded relation (works with Mongo)
        if ($employeeSearch !== '') {
            $needle = mb_strtolower($employeeSearch, 'UTF-8');

            $prets = $prets->filter(function ($p) use ($needle) {
                $name = mb_strtolower((string) ($p->employee->name ?? ''), 'UTF-8');
                $email = mb_strtolower((string) ($p->employee->email ?? ''), 'UTF-8');
                $matricule = mb_strtolower((string) ($p->employee->matricule ?? ''), 'UTF-8');

                return str_contains($name, $needle)
                    || str_contains($email, $needle)
                    || str_contains($matricule, $needle);
            })->values();
        }

        return view('admin.prets.index', compact('prets', 'status', 'employeeSearch', 'dateFrom', 'dateTo'));
    }

    public function show($id)
    {
        $pret = Pret::with(['employee', 'mensualites'])->findOrFail($id);
        return view('admin.prets.show', compact('pret'));
    }
}
