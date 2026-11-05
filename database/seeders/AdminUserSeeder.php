<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Superadmin DCMS',
                'password' => Hash::make('mahasewa@gmail.com'),
                'role' => 'superadmin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'rafiqirfq24@gmail.com'],
            [
                'name' => 'Administrator DCMS',
                'password' => Hash::make('Fitrianirafiqi1g'),
                'role' => 'superadmin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Staff Admin DCMS',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
            ],
        );
    }
}
