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
            'employee_id' => 'WEN-26-0001',
            'full_name' => 'System Administrator',
            'department' => 'admin',
            'employment_type' => 'WS',
            'status' => 'active',
            'base_salary' => 100000,
            'required_time' => '09:00',
            'track_attendance' => true,
        ]);

        Staff::create([
            'employee_id' => 'WEN-26-0002',
            'full_name' => 'HR Manager',
            'department' => 'hr',
            'employment_type' => 'WS',
            'status' => 'active',
            'base_salary' => 80000,
            'required_time' => '09:00',
            'track_attendance' => true,
        ]);

        Staff::create([
            'employee_id' => 'WEN-26-0003',
            'full_name' => 'Supervisor',
            'department' => 'academic',
            'employment_type' => 'WLS-CT',
            'status' => 'active',
            'total_classes' => 20,
            'rate_per_class' => 500,
        ]);
    }
}
