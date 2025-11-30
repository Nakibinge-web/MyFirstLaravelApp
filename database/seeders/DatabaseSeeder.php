<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in order to maintain referential integrity
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            TransactionSeeder::class,
            BudgetSeeder::class,
            GoalSeeder::class,
            NotificationSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully with demo data!');
    }
}
