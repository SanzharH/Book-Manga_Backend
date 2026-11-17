<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.test'],
            [
                'name' => 'Демо-администратор',
                'password' => Hash::make('DemoAdmin!2026'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'buyer@example.test'],
            [
                'name' => 'Демо-покупатель',
                'password' => Hash::make('DemoBuyer!2026'),
                'role' => 'buyer',
                'email_verified_at' => now(),
            ]
        );
    }
}
