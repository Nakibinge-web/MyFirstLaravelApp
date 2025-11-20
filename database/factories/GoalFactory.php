<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'target_amount' => fake()->randomFloat(2, 1000, 50000),
            'current_amount' => fake()->randomFloat(2, 0, 1000),
            'target_date' => fake()->dateTimeBetween('now', '+1 year'),
            'status' => 'active',
            'description' => fake()->sentence(),
        ];
    }
}
