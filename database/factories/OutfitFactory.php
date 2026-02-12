<?php

namespace Database\Factories;

use App\Models\OutfitTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutfitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'outfit_template_id' => OutfitTemplate::factory(),
            'name' => fake()->words(3, true),
            'occasion' => fake()->randomElement(['casual', 'formal', 'business', 'evening']),
            'notes' => fake()->optional()->sentence(),
            'harmony_score' => fake()->numberBetween(60, 100),
        ];
    }
}
