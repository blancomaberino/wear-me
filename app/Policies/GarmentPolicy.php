<?php

namespace App\Policies;

use App\Models\Garment;
use App\Models\User;

class GarmentPolicy
{
    public function view(User $user, Garment $garment): bool
    {
        return $garment->user_id === $user->id;
    }

    public function update(User $user, Garment $garment): bool
    {
        return $garment->user_id === $user->id;
    }

    public function delete(User $user, Garment $garment): bool
    {
        return $garment->user_id === $user->id;
    }
}
