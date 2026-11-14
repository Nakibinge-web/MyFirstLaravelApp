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
        $seeders = [
            UserSeeder::class,
            CategorySeeder::class,
        ];

        foreach ([
            'Database\Seeders\TransactionSeeder',
            'Database\Seeders\BudgetSeeder',
            'Database\Seeders\GoalSeeder',
            'Database\Seeders\NotificationSeeder',
        ] as $class) {
            if (class_exists($class)) {
                $seeders[] = $class;
            }
        }

        // Seed in order to maintain referential integrity
        $this->call($seeders);

        $this->command->info('✅ Database seeded successfully with demo data!');
    }
}
