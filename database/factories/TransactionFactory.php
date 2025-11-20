<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'type' => fake()->randomElement(['income', 'expense']),
            'description' => fake()->sentence(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
