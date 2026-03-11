<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeTypeConfig;
use App\Models\Mensualite;
use App\Models\Pret;
use App\Services\PretHajjService;
use Illuminate\Http\Request;

class PretHajjController extends Controller
{
    public function __construct(private PretHajjService $service) {}

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

    private function computeLimits(Request $request, int $months, float $sumCnss4Sem): array
    {
        $user = $request->user();
        $salary = (float) ($user->salaire ?? 0);

        $PLAFOND_SOCIETE = 10000.0;
        $INTEREST_RATE = 0.04;

        $plafondSociete = $PLAFOND_SOCIETE;
        $plafondCnss = $sumCnss4Sem / 6.0;
        $salary40 = $salary * 0.4;

        $activePretIds = Pret::query()
            ->where('employeeId', (string) $user->id)
            ->whereIn('status', ['active', 'approved', 'Actif', 'Approuvé'])
            ->pluck('_id')
            ->toArray();

        $sumExistingMensualites = 0.0;
        if (!empty($activePretIds)) {
            $sumExistingMensualites = (float) Mensualite::query()
                ->where('employeeId', (string) $user->id)
                ->whereIn('pretId', $activePretIds)
                ->whereIn('status', ['pending', 'En attente', 'Actif', 'Approuvé'])
                ->sum('amount');
        }

        $monthlyCapacity = max(0.0, $salary40 - $sumExistingMensualites);

        $maxByMensualite = ($monthlyCapacity > 0)
            ? ($monthlyCapacity * $months) / (1.0 + $INTEREST_RATE)
            : 0.0;

        $maxAmount = min($plafondSociete, $plafondCnss, $maxByMensualite);
        $maxAmount = max(0.0, $maxAmount);

        $typeMax = DemandeTypeConfig::maxAmountFor('pret-hajj', 5000.0);
        if ($typeMax !== null) {
            $maxAmount = min($maxAmount, $typeMax);
        }

        return [
            'interestRate' => $INTEREST_RATE,
            'monthlyCapacity' => $monthlyCapacity,
            'maxAmount' => $maxAmount,
            'typeMaxAmount' => $typeMax,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amountRequested' => 'required|numeric|min:0.01',
            'dureeMonths' => 'required|integer|min:1|max:60',

            // ✅ CNSS rules
            'sumCnss4Sem' => 'required|numeric|min:0.01',
            'cnssAttachmentIds' => 'required|array|size:4',
            'cnssAttachmentIds.*' => 'string',

            'reason' => 'nullable|string|max:255',
            'familySize' => 'nullable|integer|min:0',
            'monthlyIncome' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',

            // other attachments optional
            'attachmentIds' => 'nullable|array',
            'attachmentIds.*' => 'string',
        ]);

        $months = (int) $data['dureeMonths'];
        $amount = (float) $data['amountRequested'];
        $sumCnss4Sem = (float) $data['sumCnss4Sem'];

        $limits = $this->computeLimits($request, $months, $sumCnss4Sem);
        $interestRate = (float) $limits['interestRate'];
        $monthlyCapacity = (float) $limits['monthlyCapacity'];
        $maxAmount = (float) $limits['maxAmount'];

        if ($amount > $maxAmount) {
            return response()->json([
                'message' => "Montant dépasse le max autorisé (" . number_format($maxAmount, 2, '.', '') . " TND)."
            ], 422);
        }

        $totalToRepay = $amount * (1.0 + $interestRate);
        $mensualite = ($months > 0) ? ($totalToRepay / $months) : $totalToRepay;

        if ($monthlyCapacity <= 0) {
            return response()->json([
                'message' => "Vous n'avez plus de capacité mensuelle (40% salaire déjà consommé)."
            ], 422);
        }

        if ($mensualite > $monthlyCapacity + 0.0001) {
            return response()->json([
                'message' => "Durée insuffisante. Mensualité (" . number_format($mensualite, 2, '.', '') .
                    " TND) dépasse votre capacité (" . number_format($monthlyCapacity, 2, '.', '') . " TND)."
            ], 422);
        }

        $data['employeeId'] = (string) $request->user()->id;
        $data['status'] = 'En attente';
        $data['submittedAt'] = now();

        // optional helpful stored fields
        $data['interestRate'] = $interestRate;
        $data['totalToRepay'] = round($totalToRepay, 2);
        $data['mensualiteExpected'] = round($mensualite, 2);

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
            'familySize' => 'nullable|integer|min:0',
            'monthlyIncome' => 'nullable|numeric|min:0',
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
