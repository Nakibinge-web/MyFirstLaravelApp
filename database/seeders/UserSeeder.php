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
        // Create demo user with known credentials
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@financialtracker.com',
            'password' => Hash::make('password'),
            'currency' => 'USD',
            'email_verified_at' => now(),
        ]);

        // Create test user
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'currency' => 'EUR',
            'email_verified_at' => now(),
        ]);

        // Create additional test users with different currencies
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'currency' => 'GBP',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'currency' => 'INR',
            'email_verified_at' => now(),
        ]);

        $this->command->info('👤 Users seeded successfully!');
    }
}
