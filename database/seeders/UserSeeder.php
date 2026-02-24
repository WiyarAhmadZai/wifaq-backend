<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@wifaq.edu',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Sarah Jenkins',
            'email' => 'sarah@wifaq.edu',
            'password' => Hash::make('password'),
        ]);
    }
}
