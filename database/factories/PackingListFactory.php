<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackingListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'destination' => fake()->city(),
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(14),
            'occasions' => ['casual', 'formal'],
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
