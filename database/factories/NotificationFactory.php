<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['budget_alert', 'goal_reminder', 'goal_achieved']),
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
            'icon' => '🔔',
            'color' => fake()->randomElement(['blue', 'green', 'yellow', 'red']),
            'is_read' => false,
        ];
    }
}
