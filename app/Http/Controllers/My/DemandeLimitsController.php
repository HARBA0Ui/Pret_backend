<?php

namespace App\Http\Controllers\My;

use App\Http\Controllers\Controller;
use App\Models\DemandeTypeConfig;
use App\Models\Mensualite;
use App\Models\Pret;
use Illuminate\Http\Request;

class DemandeLimitsController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'months' => 'required|integer|min:1|max:60',
            'sumCnss4Sem' => 'required|numeric|min:0.01',
            'amount' => 'nullable|numeric|min:0.01',
            'type' => 'nullable|string',
        ]);

        $months = (int) $data['months'];
        $sumCnss4Sem = (float) $data['sumCnss4Sem'];
        $amount = isset($data['amount']) ? (float) $data['amount'] : null;
        $type = (string) ($data['type'] ?? '');

        $user = $request->user();
        $salary = (float) ($user->salaire ?? 0);

        $PLAFOND_SOCIETE = 10000.0;
        $INTEREST_RATE = 0.04;

        $plafondSociete = $PLAFOND_SOCIETE;
        $plafondCnss = $sumCnss4Sem / 6.0;

        // 40% salary
        $salary40 = $salary * 0.4;

        // Sum mensualites ONLY for ACTIVE loans
        $activePretIds = Pret::query()
            ->where('employeeId', (string) $user->id)
            ->where('status', 'active')
            ->pluck('_id')
            ->toArray();

        $sumExistingMensualites = 0.0;

        if (!empty($activePretIds)) {
            $sumExistingMensualites = (float) Mensualite::query()
                ->where('employeeId', (string) $user->id)
                ->whereIn('pretId', $activePretIds)
                ->where('status', 'pending')
                ->sum('amount');
        }

        $monthlyCapacity = max(0.0, $salary40 - $sumExistingMensualites);

        // Amount bound by mensualite rule:
        // newMensualite = (amount*(1+interest))/months <= monthlyCapacity
        // => amount <= monthlyCapacity*months/(1+interest)
        $maxByMensualite = ($monthlyCapacity > 0)
            ? ($monthlyCapacity * $months) / (1.0 + $INTEREST_RATE)
            : 0.0;

        $maxAmount = min($plafondSociete, $plafondCnss, $maxByMensualite);
        $maxAmount = max(0.0, $maxAmount);

        $typeMaxAmount = null;
        if ($type === 'pret-hajj') {
            $typeMaxAmount = DemandeTypeConfig::maxAmountFor('pret-hajj', 5000.0);
            if ($typeMaxAmount !== null) {
                $maxAmount = min($maxAmount, (float) $typeMaxAmount);
            }
        }

        $minMonthsSuggested = null;
        if ($amount !== null && $monthlyCapacity > 0) {
            $totalToRepay = $amount * (1.0 + $INTEREST_RATE);
            $minMonths = (int) ceil($totalToRepay / $monthlyCapacity);
            if ($minMonths < 1) $minMonths = 1;
            if ($minMonths > 60) $minMonths = 60;
            $minMonthsSuggested = $minMonths;
        }

        return response()->json([
            'plafondSociete' => round($plafondSociete, 2),
            'plafondCnss' => round($plafondCnss, 2),
            'salary' => round($salary, 2),
            'salary40' => round($salary40, 2),
            'sumExistingMensualites' => round($sumExistingMensualites, 2),
            'monthlyCapacity' => round($monthlyCapacity, 2),
            'interestRate' => $INTEREST_RATE,
            'months' => $months,
            'maxByMensualite' => round($maxByMensualite, 2),
            'maxAmount' => round($maxAmount, 2),
            'typeMaxAmount' => $typeMaxAmount !== null ? round((float) $typeMaxAmount, 2) : null,
            'minMonthsSuggested' => $minMonthsSuggested,
        ]);
    }
}
