<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'patient@example.test'],
            ['name' => 'Demo Patient', 'password' => Hash::make('password123')],
        );
    }
}
