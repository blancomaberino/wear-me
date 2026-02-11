<?php

namespace Database\Factories;

use App\Enums\ProcessingStatus;
use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\TryOnResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TryOnVideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tryon_result_id' => TryOnResult::factory(),
            'model_image_id' => ModelImage::factory(),
            'garment_id' => Garment::factory(),
            'kling_task_id' => 'vtask_' . fake()->uuid(),
            'status' => ProcessingStatus::Pending,
            'video_path' => null,
            'duration_seconds' => null,
            'error_message' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ProcessingStatus::Completed,
            'video_path' => 'tryon-videos/' . fake()->uuid() . '.mp4',
            'duration_seconds' => 5,
        ]);
    }
}
