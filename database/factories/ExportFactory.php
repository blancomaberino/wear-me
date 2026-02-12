<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'file_path' => null,
            'status' => 'pending',
            'file_size_bytes' => null,
            'include_images' => true,
            'include_results' => true,
            'expires_at' => now()->addDays(7),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'file_path' => 'exports/' . fake()->uuid() . '.zip',
            'file_size_bytes' => fake()->numberBetween(1000000, 50000000),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}
