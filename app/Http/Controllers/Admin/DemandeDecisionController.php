<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AideSociale;
use App\Models\DonsScolaire;
use App\Models\PretExceptionnel;
use App\Models\PretHajj;
use App\Models\RemboursementAnticipe;
use App\Services\PretCreationService;
use App\Services\RemboursementAnticipeService;
use Illuminate\Http\Request;

class DemandeDecisionController extends Controller
{
    public function __construct(
        private PretCreationService $pretCreationService,
        private RemboursementAnticipeService $remboursementAnticipeService
    ) {}

    private function resolveModel(string $type)
    {
        return match ($type) {
            'aide-sociale' => AideSociale::class,
            'don-scolaire' => DonsScolaire::class,
            'pret-exceptionnel' => PretExceptionnel::class,
            'pret-hajj' => PretHajj::class,
            'remboursement',
            'remboursement-anticipe' => RemboursementAnticipe::class,
            default => null,
        };
    }

    private function isRemboursementType(string $type): bool
    {
        return in_array($type, ['remboursement', 'remboursement-anticipe'], true);
    }

    public function approuver(Request $request, string $id)
    {
        $type = (string) $request->get('type', '');
        $modelClass = $this->resolveModel($type);

        if (!$modelClass) {
            return back()->withErrors(['type' => 'Type de demande invalide.']);
        }

        $data = $request->validate([
            'approvedAmount' => 'nullable|numeric|min:0.01',
        ]);

        $demande = $modelClass::with('employee')->findOrFail($id);

        if (($demande->status ?? '') !== 'En attente') {
            return back()->withErrors(['status' => 'Cette demande a déjà été traitée.']);
        }

        $approvedAmount = (isset($data['approvedAmount']) && $data['approvedAmount'] !== null)
            ? (float) $data['approvedAmount']
            : (float) ($demande->amountRequested ?? 0);

        if ($approvedAmount <= 0) {
            return back()->withErrors(['approvedAmount' => 'Montant approuvé invalide.']);
        }

        if ($this->isRemboursementType($type)) {
            try {
                $this->remboursementAnticipeService->approveAndApplyToPret(
                    $demande,
                    (string) $request->user()->id,
                    $approvedAmount
                );
            } catch (\RuntimeException $e) {
                return back()->withErrors(['remboursement' => $e->getMessage()]);
            }

            return back()->with(
                'success',
                'Demande approuvée. Le remboursement anticipé a été appliqué au prêt sélectionné.'
            );
        }

        $dureeMonths = (int) ($demande->dureeMonths ?? 0);
        if ($dureeMonths <= 0) {
            return back()->withErrors([
                'dureeMonths' => 'Durée invalide (dureeMonths manquant ou incorrect).',
            ]);
        }

        $demande->update([
            'status' => 'Approuvée',
            'approvedAmount' => $approvedAmount,
            'reviewedAt' => now(),
            'decisionAt' => now(),
            'reviewByAdminId' => (string) $request->user()->id,
        ]);

        $this->pretCreationService->createFromDemande($demande, $type);

        return back()->with(
            'success',
            'Demande approuvée. Prêt et échéances générés automatiquement.'
        );
    }

    public function rejeter(Request $request, string $id)
    {
        $type = (string) $request->get('type', '');
        $modelClass = $this->resolveModel($type);

        if (!$modelClass) {
            return back()->withErrors(['type' => 'Type de demande invalide.']);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Le motif de rejet est obligatoire.',
        ]);

        $demande = $modelClass::findOrFail($id);

        if (($demande->status ?? '') !== 'En attente') {
            return back()->withErrors(['status' => 'Cette demande a déjà été traitée.']);
        }

        $demande->update([
            'status' => 'Rejetée',
            'reason' => $data['reason'],
            'reviewedAt' => now(),
            'decisionAt' => now(),
            'reviewByAdminId' => (string) $request->user()->id,
        ]);

        return back()->with('success', 'Demande rejetée.');
    }
}
