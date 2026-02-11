<?php

namespace Database\Factories;

use App\Enums\ProcessingStatus;
use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TryOnResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'model_image_id' => ModelImage::factory(),
            'garment_id' => Garment::factory(),
            'provider_task_id' => 'task_' . fake()->uuid(),
            'provider' => 'kling',
            'status' => ProcessingStatus::Pending,
            'result_path' => null,
            'error_message' => null,
            'is_favorite' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ProcessingStatus::Completed,
            'result_path' => 'tryon-results/' . fake()->uuid() . '.jpg',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => ProcessingStatus::Failed,
            'error_message' => 'Processing failed',
        ]);
    }

    public function favorite(): static
    {
        return $this->state(fn () => ['is_favorite' => true]);
    }
}
