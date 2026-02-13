<?php

namespace Database\Factories;

use App\Models\Lookbook;
use App\Models\TryOnResult;
use Illuminate\Database\Eloquent\Factories\Factory;

class LookbookItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lookbook_id' => Lookbook::factory(),
            'itemable_type' => TryOnResult::class,
            'itemable_id' => TryOnResult::factory(),
            'note' => fake()->optional()->sentence(),
            'sort_order' => 0,
        ];
    }
}
