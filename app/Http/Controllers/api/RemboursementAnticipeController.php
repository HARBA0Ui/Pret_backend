<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pret;
use App\Services\RemboursementAnticipeService;
use Illuminate\Http\Request;

class RemboursementAnticipeController extends Controller
{
    public function __construct(private RemboursementAnticipeService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        return response()->json($this->service->paginate($perPage));
    }

    public function myIndex(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $user = $request->user();

        return response()->json(
            $this->service->paginateByEmployee((string) $user->id, $perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loanIdToRepay' => 'required|string',
            'earlyRepaymentAmount' => 'required|numeric|min:0.01',
            'penaltyWaived' => 'nullable|boolean',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'attachmentIds' => 'nullable|array',
            'attachmentIds.*' => 'string',
        ]);

        $employeeId = (string) $request->user()->id;
        $pret = Pret::query()
            ->where('employeeId', $employeeId)
            ->find($data['loanIdToRepay']);

        if (!$pret) {
            return response()->json([
                'message' => 'Prêt introuvable pour cet employé.',
            ], 422);
        }

        $status = mb_strtolower((string) ($pret->status ?? ''), 'UTF-8');
        $allowedStatuses = ['active', 'actif', 'approved', 'approuvé', 'approuve'];
        if (!in_array($status, $allowedStatuses, true)) {
            return response()->json([
                'message' => 'Ce prêt ne peut pas être remboursé de façon anticipée.',
            ], 422);
        }

        $remainingBalance = $this->service->computeRemainingBalanceForPret($pret);
        if ($remainingBalance <= 0) {
            return response()->json([
                'message' => 'Ce prêt n\'a plus de solde restant.',
            ], 422);
        }

        $earlyAmount = (float) $data['earlyRepaymentAmount'];
        if ($earlyAmount > $remainingBalance + 0.0001) {
            return response()->json([
                'message' => 'Le montant anticipé dépasse le solde restant du prêt.',
            ], 422);
        }

        $data['employeeId'] = $employeeId;
        $data['amountRequested'] = $earlyAmount;
        $data['dureeMonths'] = 1;
        $data['status'] = 'En attente';
        $data['submittedAt'] = now();

        return response()->json($this->service->create($data), 201);
    }

    public function show(string $id)
    {
        return response()->json($this->service->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'amountRequested' => 'nullable|numeric|min:0.01',
            'dureeMonths' => 'nullable|integer|min:1|max:60',
            'loanIdToRepay' => 'nullable|string',
            'earlyRepaymentAmount' => 'nullable|numeric|min:0',
            'penaltyWaived' => 'nullable|boolean',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(string $id)
    {
        $this->service->delete($id);
        return response()->noContent();
    }
}
