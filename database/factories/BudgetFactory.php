<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    public function definition(): array
    {
        $startDate = now()->startOfMonth();
        
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'period' => 'monthly',
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->endOfMonth(),
        ];
    }
}
