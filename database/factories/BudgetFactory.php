<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    public function definition(): array
    {
        $period = fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']);
        $startDate = now()->startOfDay();
        
        $endDate = match($period) {
            'daily' => $startDate->copy()->endOfDay(),
            'weekly' => $startDate->copy()->addDays(6)->endOfDay(),
            'monthly' => $startDate->copy()->addDays(29)->endOfDay(),
            'yearly' => $startDate->copy()->addDays(364)->endOfDay(),
        };
        
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
