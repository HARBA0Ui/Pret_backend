<?php

namespace Database\Seeders;

use App\Models\Mensualite;
use App\Models\Pret;
use Illuminate\Database\Seeder;

class MensualiteSeeder extends Seeder
{
    public function run(): void
    {
        $pret = Pret::first();

        if ($pret) {
            $monthlyAmount = $pret->amount / $pret->durationMonths;
            $currentDate = $pret->startDate;

            for ($i = 1; $i <= $pret->durationMonths; $i++) {
                $dueDate = $currentDate->copy()->addMonths($i);
                
                Mensualite::create([
                    'pretId' => $pret->id,
                    'employeeId' => $pret->employeeId,
                    'paymentNumber' => $i,
                    'dueDate' => $dueDate,
                    'amount' => $monthlyAmount,
                    'status' => $i <= 1 ? 'paid' : 'pending',
                    'paidDate' => $i <= 1 ? now()->subMonths(1) : null,
                    'paidAmount' => $i <= 1 ? $monthlyAmount : null,
                ]);
            }
        }
    }
}
