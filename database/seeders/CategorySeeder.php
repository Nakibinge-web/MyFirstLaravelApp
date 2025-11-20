<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and create default categories for each
        $users = \App\Models\User::all();

        $defaultCategories = [
            // Expense categories
            ['name' => 'Food & Dining', 'type' => 'expense', 'color' => '#FF6B6B', 'icon' => '🍔', 'is_default' => true],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#4ECDC4', 'icon' => '🚗', 'is_default' => true],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#95E1D3', 'icon' => '🛍️', 'is_default' => true],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#F38181', 'icon' => '🎬', 'is_default' => true],
            ['name' => 'Bills & Utilities', 'type' => 'expense', 'color' => '#AA96DA', 'icon' => '💡', 'is_default' => true],
            ['name' => 'Healthcare', 'type' => 'expense', 'color' => '#FCBAD3', 'icon' => '🏥', 'is_default' => true],
            ['name' => 'Education', 'type' => 'expense', 'color' => '#A8D8EA', 'icon' => '📚', 'is_default' => true],
            ['name' => 'Housing', 'type' => 'expense', 'color' => '#FFD93D', 'icon' => '🏠', 'is_default' => true],
            
            // Income categories
            ['name' => 'Salary', 'type' => 'income', 'color' => '#6BCF7F', 'icon' => '💰', 'is_default' => true],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#4D96FF', 'icon' => '💼', 'is_default' => true],
            ['name' => 'Investment', 'type' => 'income', 'color' => '#FFB84C', 'icon' => '📈', 'is_default' => true],
            ['name' => 'Other Income', 'type' => 'income', 'color' => '#A459D1', 'icon' => '💵', 'is_default' => true],
        ];

        foreach ($users as $user) {
            foreach ($defaultCategories as $category) {
                \App\Models\Category::create(array_merge($category, ['user_id' => $user->id]));
            }
        }
    }

    public static function createForUser($userId): void
    {
        $defaultCategories = [
            // Expense categories
            ['name' => 'Food & Dining', 'type' => 'expense', 'color' => '#FF6B6B', 'icon' => '🍔', 'is_default' => true],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#4ECDC4', 'icon' => '🚗', 'is_default' => true],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#95E1D3', 'icon' => '🛍️', 'is_default' => true],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#F38181', 'icon' => '🎬', 'is_default' => true],
            ['name' => 'Bills & Utilities', 'type' => 'expense', 'color' => '#AA96DA', 'icon' => '💡', 'is_default' => true],
            ['name' => 'Healthcare', 'type' => 'expense', 'color' => '#FCBAD3', 'icon' => '🏥', 'is_default' => true],
            ['name' => 'Education', 'type' => 'expense', 'color' => '#A8D8EA', 'icon' => '📚', 'is_default' => true],
            ['name' => 'Housing', 'type' => 'expense', 'color' => '#FFD93D', 'icon' => '🏠', 'is_default' => true],
            
            // Income categories
            ['name' => 'Salary', 'type' => 'income', 'color' => '#6BCF7F', 'icon' => '💰', 'is_default' => true],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#4D96FF', 'icon' => '💼', 'is_default' => true],
            ['name' => 'Investment', 'type' => 'income', 'color' => '#FFB84C', 'icon' => '📈', 'is_default' => true],
            ['name' => 'Other Income', 'type' => 'income', 'color' => '#A459D1', 'icon' => '💵', 'is_default' => true],
        ];

        foreach ($defaultCategories as $category) {
            \App\Models\Category::create(array_merge($category, ['user_id' => $userId]));
        }
    }
}
