<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LookbookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'cover_image_path' => null,
            'is_public' => false,
        ];
    }

    public function public(): static
    {
        return $this->state(fn () => ['is_public' => true]);
    }
}
