<?php

namespace Database\Seeders;

use App\Models\AideSociale;
use App\Models\DonsScolaire;
use App\Models\SalaireCollectives;
use App\Models\RemboursementAnticipe;
use Illuminate\Database\Seeder;

class DemandeSeeder extends Seeder
{
    public function run(): void
    {
        // Get employee IDs (from UserSeeder)
        $employees = \App\Models\User::where('role', 'employee')->get();
        $admin = \App\Models\User::where('role', 'admin')->first();

        // Create AideSociale demandes
        AideSociale::create([
            'status' => 'approved',
            'employeeId' => $employees[0]->id,
            'amountRequested' => 5000,
            'approvedAmount' => 4500,
            'submittedAt' => now()->subMonths(2),
            'reviewedAt' => now()->subMonths(1),
            'decisionAt' => now()->subMonths(1),
            'reviewByAdminId' => $admin->id,
            'reasonCode' => 'HEALTH',
            'category' => 'Medical Emergency',
            'familySize' => 4,
            'monthlyIncome' => 3000,
        ]);

        AideSociale::create([
            'status' => 'pending',
            'employeeId' => $employees[1]->id,
            'amountRequested' => 3000,
            'approvedAmount' => null,
            'submittedAt' => now()->subDays(5),
            'reviewedAt' => null,
            'decisionAt' => null,
            'reviewByAdminId' => null,
            'reasonCode' => 'EMERGENCY',
            'category' => 'Urgent Needs',
            'familySize' => 2,
            'monthlyIncome' => 2800,
        ]);

        // Create DonsScolaire demandes
        DonsScolaire::create([
            'status' => 'approved',
            'employeeId' => $employees[0]->id,
            'amountRequested' => 2000,
            'approvedAmount' => 2000,
            'submittedAt' => now()->subMonths(3),
            'reviewedAt' => now()->subMonths(2),
            'decisionAt' => now()->subMonths(2),
            'reviewByAdminId' => $admin->id,
            'studentName' => 'Zainab Ben Ali',
            'schoolName' => 'University of Tunis',
            'schoolLevel' => 'University',
            'academicYear' => '2025-2026',
            'tuitionAmount' => 2000,
            'purpose' => 'Tuition',
        ]);

        DonsScolaire::create([
            'status' => 'rejected',
            'employeeId' => $employees[2]->id,
            'amountRequested' => 1500,
            'approvedAmount' => null,
            'submittedAt' => now()->subMonths(1),
            'reviewedAt' => now()->subMonths(1),
            'decisionAt' => now()->subMonths(1),
            'reviewByAdminId' => $admin->id,
            'reason' => 'Student already received aid this year',
            'studentName' => 'Karim Tarhouni',
            'schoolName' => 'Secondary School',
            'schoolLevel' => 'Secondary',
            'academicYear' => '2024-2025',
            'tuitionAmount' => 1500,
            'purpose' => 'Books and supplies',
        ]);

        // Create SalaireCollectives demandes
        SalaireCollectives::create([
            'status' => 'approved',
            'employeeId' => $employees[1]->id,
            'amountRequested' => 4000,
            'approvedAmount' => 4000,
            'submittedAt' => now()->subMonths(4),
            'reviewedAt' => now()->subMonths(3),
            'decisionAt' => now()->subMonths(3),
            'reviewByAdminId' => $admin->id,
            'collectiveAgreement' => 'General Labor Agreement 2024',
            'beneficiaryType' => 'All Employees',
            'disbursementSchedule' => 'Monthly',
        ]);

        // Create RemboursementAnticipe demande
        RemboursementAnticipe::create([
            'status' => 'pending',
            'employeeId' => $employees[0]->id,
            'amountRequested' => 6000,
            'approvedAmount' => null,
            'submittedAt' => now()->subDays(3),
            'reviewedAt' => null,
            'decisionAt' => null,
            'reviewByAdminId' => null,
            'loanIdToRepay' => 'test_loan_id_001',
            'earlyRepaymentAmount' => 6000,
            'penaltyWaived' => false,
        ]);
    }
}
