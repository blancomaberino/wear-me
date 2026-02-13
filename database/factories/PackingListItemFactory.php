<?php

namespace Database\Factories;

use App\Models\Garment;
use App\Models\PackingList;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackingListItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'packing_list_id' => PackingList::factory(),
            'garment_id' => Garment::factory(),
            'day_number' => fake()->numberBetween(1, 7),
            'occasion' => fake()->randomElement(['casual', 'formal', 'business', 'evening']),
            'is_packed' => false,
        ];
    }

    public function packed(): static
    {
        return $this->state(fn () => ['is_packed' => true]);
    }
}
