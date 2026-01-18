<?php

namespace Database\Seeders;

use App\Models\Pret;
use App\Models\AideSociale;
use Illuminate\Database\Seeder;

class PretSeeder extends Seeder
{
    public function run(): void
    {
        $employees = \App\Models\User::where('role', 'employee')->get();
        $demande = AideSociale::first(); // Get first approved demande

        if ($demande) {
            Pret::create([
                'demandeId' => $demande->id,
                'employeeId' => $employees[0]->id,
                'amount' => 4500,
                'interestRate' => 5.0,
                'durationMonths' => 12,
                'startDate' => now()->subMonths(1),
                'endDate' => now()->addMonths(11),
                'status' => 'active',
                'remainingBalance' => 4050,
                'totalPaid' => 450,
            ]);
        }
    }
}
