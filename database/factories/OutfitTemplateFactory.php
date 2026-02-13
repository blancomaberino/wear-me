<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutfitTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->words(2, true),
            'occasion' => fake()->randomElement(['casual', 'formal', 'business', 'evening']),
            'description' => fake()->sentence(),
            'slots' => [
                ['label' => 'Top', 'category' => 'upper'],
                ['label' => 'Bottom', 'category' => 'lower'],
            ],
            'icon' => 'IconShirt',
            'is_system' => true,
        ];
    }

    public function userOwned(): static
    {
        return $this->state(fn () => [
            'user_id' => User::factory(),
            'is_system' => false,
        ]);
    }
}
