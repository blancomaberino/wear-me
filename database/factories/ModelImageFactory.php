<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'path' => 'model-images/' . fake()->uuid() . '.jpg',
            'original_filename' => fake()->word() . '.jpg',
            'thumbnail_path' => 'model-images/thumbnails/thumb_' . fake()->uuid() . '.jpg',
            'width' => 800,
            'height' => 1200,
            'size_bytes' => fake()->numberBetween(100000, 5000000),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}
