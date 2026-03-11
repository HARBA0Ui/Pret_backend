<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AideSociale;
use App\Models\DonsScolaire;
use App\Models\PretExceptionnel;
use App\Models\PretHajj;
use App\Models\RemboursementAnticipe;
use App\Models\Pret;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // ---- totals demandes
        $totalAide = AideSociale::count();
        $totalDon  = DonsScolaire::count();
        $totalExceptionnel = PretExceptionnel::count();
        $totalHajj = PretHajj::count();
        $totalRemb = RemboursementAnticipe::count();

        $totalDemandes = $totalAide + $totalDon + $totalExceptionnel + $totalHajj + $totalRemb;

        // ---- demandes by status (all demandes)
        $pendingDemandes  = AideSociale::where('status','En attente')->count()
                         + DonsScolaire::where('status','En attente')->count()
                         + PretExceptionnel::where('status','En attente')->count()
                         + PretHajj::where('status','En attente')->count()
                         + RemboursementAnticipe::where('status','En attente')->count();

        $approvedDemandes = AideSociale::where('status','Approuvée')->count()
                         + DonsScolaire::where('status','Approuvée')->count()
                         + PretExceptionnel::where('status','Approuvée')->count()
                         + PretHajj::where('status','Approuvée')->count()
                         + RemboursementAnticipe::where('status','Approuvée')->count();

        $rejectedDemandes = AideSociale::where('status','Rejetée')->count()
                         + DonsScolaire::where('status','Rejetée')->count()
                         + PretExceptionnel::where('status','Rejetée')->count()
                         + PretHajj::where('status','Rejetée')->count()
                         + RemboursementAnticipe::where('status','Rejetée')->count();

        // ---- prets
        $totalPrets  = Pret::count();
        $activePrets = Pret::where('status', 'active')->count();
        $completedPrets = Pret::where('status', 'completed')->count();
        $defaultedPrets = Pret::where('status', 'defaulted')->count();

        // helper %
        $pct = function($part, $whole) {
            if ($whole <= 0) return 0;
            return round(($part / $whole) * 100, 1);
        };

        $stats = [
            'total_demandes'   => $totalDemandes,
            'pending_demandes' => $pendingDemandes,
            'approved_demandes'=> $approvedDemandes,
            'rejected_demandes'=> $rejectedDemandes,
            'pending_registrations' => User::where('role', 'employee')->where('isEmployeeAccepted', false)->count(),

            'pending_demandes_pct'  => $pct($pendingDemandes, $totalDemandes),
            'approved_demandes_pct' => $pct($approvedDemandes, $totalDemandes),
            'rejected_demandes_pct' => $pct($rejectedDemandes, $totalDemandes),

            'total_prets'      => $totalPrets,
            'active_prets'     => $activePrets,
            'completed_prets'  => $completedPrets,
            'defaulted_prets'  => $defaultedPrets,

            'active_prets_pct'    => $pct($activePrets, $totalPrets),
            'completed_prets_pct' => $pct($completedPrets, $totalPrets),
            'defaulted_prets_pct' => $pct($defaultedPrets, $totalPrets),
        ];

        $charts = [
            'demandesByType' => [
                'Aide Sociale' => $totalAide,
                'Don Scolaire' => $totalDon,
                'Prêt Exceptionnel' => $totalExceptionnel,
                'Prêt Hajj' => $totalHajj,
                'Remboursement Anticipé' => $totalRemb,
            ],

            // ✅ keep counts (charts use counts), but we also send percentages
            'demandesByStatus' => [
                'En attente' => $pendingDemandes,
                'Approuvée'  => $approvedDemandes,
                'Rejetée'    => $rejectedDemandes,
            ],
            'demandesByStatusPct' => [
                'En attente' => $pct($pendingDemandes, $totalDemandes),
                'Approuvée'  => $pct($approvedDemandes, $totalDemandes),
                'Rejetée'    => $pct($rejectedDemandes, $totalDemandes),
            ],

            'pretsByStatus' => [
                'Actif'     => $activePrets,
                'Complété'  => $completedPrets,
                'En défaut' => $defaultedPrets,
            ],
            'pretsByStatusPct' => [
                'Actif'     => $pct($activePrets, $totalPrets),
                'Complété'  => $pct($completedPrets, $totalPrets),
                'En défaut' => $pct($defaultedPrets, $totalPrets),
            ],

            // if you already have evolution arrays, keep them.
            // monthsLabels / demandesPerMonth / pretsPerMonth
        ];

        return view('admin.dashboard', compact('stats', 'charts'));
    }
}
