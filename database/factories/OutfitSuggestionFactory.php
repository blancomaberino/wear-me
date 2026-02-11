<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutfitSuggestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'garment_ids' => [1, 2],
            'suggestion_text' => fake()->paragraph(),
            'occasion' => fake()->randomElement(['casual', 'work', 'evening', 'sport', 'date']),
            'is_saved' => false,
        ];
    }

    public function saved(): static
    {
        return $this->state(fn () => ['is_saved' => true]);
    }
}
