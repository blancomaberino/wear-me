<?php

namespace Database\Factories;

use App\Models\Lookbook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShareLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shareable_type' => Lookbook::class,
            'shareable_id' => Lookbook::factory(),
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'is_active' => true,
            'view_count' => 0,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function permanent(): static
    {
        return $this->state(fn () => ['expires_at' => null]);
    }
}
