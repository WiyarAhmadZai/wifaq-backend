<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        Staff::create([
            'employee_id' => 'EMP001',
            'full_name' => 'System Administrator',
            'email' => 'admin@wifaq.edu',
            'phone' => '+93 700 000 001',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'role' => 'super_admin',
            'department' => 'admin',
            'designation' => 'System Administrator',
            'hire_date' => '2024-01-01',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 100000,
        ]);

        Staff::create([
            'employee_id' => 'EMP002',
            'full_name' => 'HR Manager',
            'email' => 'hr@wifaq.edu',
            'phone' => '+93 700 000 002',
            'password' => Hash::make('password'),
            'gender' => 'female',
            'role' => 'hr_manager',
            'department' => 'hr',
            'designation' => 'HR Manager',
            'hire_date' => '2024-01-01',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 80000,
        ]);

        Staff::create([
            'employee_id' => 'EMP003',
            'full_name' => 'Supervisor',
            'email' => 'supervisor@wifaq.edu',
            'phone' => '+93 700 000 003',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'role' => 'supervisor',
            'department' => 'academic',
            'designation' => 'Academic Supervisor',
            'hire_date' => '2024-01-01',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 70000,
        ]);
    }
}
