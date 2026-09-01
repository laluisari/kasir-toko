<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@kasir.test'],
            [
                'name' => 'Admin Kasir',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Kasir user
        User::updateOrCreate(
            ['email' => 'kasir@kasir.test'],
            [
                'name' => 'Kasir 1',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );

        // Additional kasir user
        User::updateOrCreate(
            ['email' => 'kasir2@kasir.test'],
            [
                'name' => 'Kasir 2',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );
    }
}
