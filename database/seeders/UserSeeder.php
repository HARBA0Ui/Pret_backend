<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'nom' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'matricule' => 'ADM001',
            'salaire' => '10000',
            'date_joined' => now(),
            'direction' => 'Administration',
        ]);

        // Create employee users
        User::create([
            'nom' => 'Ahmed Ben Ali',
            'email' => 'ahmed@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
            'matricule' => 'EMP001',
            'salaire' => '3000',
            'date_joined' => now()->subMonths(6),
            'direction' => 'IT',
        ]);

        User::create([
            'nom' => 'Fatima Mansour',
            'email' => 'fatima@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
            'matricule' => 'EMP002',
            'salaire' => '2800',
            'date_joined' => now()->subMonths(12),
            'direction' => 'Finance',
        ]);

        User::create([
            'nom' => 'Mohamed Tarhouni',
            'email' => 'mohamed@example.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
            'matricule' => 'EMP003',
            'salaire' => '3200',
            'date_joined' => now()->subMonths(3),
            'direction' => 'HR',
        ]);
    }
}
