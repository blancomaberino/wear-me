<?php

namespace Database\Factories;

use App\Enums\GarmentCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GarmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'path' => 'garments/' . fake()->uuid() . '.jpg',
            'original_filename' => fake()->word() . '.jpg',
            'thumbnail_path' => 'garments/thumbnails/thumb_' . fake()->uuid() . '.jpg',
            'category' => fake()->randomElement(GarmentCategory::cases()),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'color_tags' => [fake()->colorName(), fake()->colorName()],
        ];
    }

    public function upper(): static
    {
        return $this->state(fn () => ['category' => GarmentCategory::Upper]);
    }

    public function lower(): static
    {
        return $this->state(fn () => ['category' => GarmentCategory::Lower]);
    }

    public function dress(): static
    {
        return $this->state(fn () => ['category' => GarmentCategory::Dress]);
    }
}
