<?php

namespace App\Policies;

use App\Models\ModelImage;
use App\Models\User;

class ModelImagePolicy
{
    public function update(User $user, ModelImage $modelImage): bool
    {
        return $modelImage->user_id === $user->id;
    }

    public function delete(User $user, ModelImage $modelImage): bool
    {
        return $modelImage->user_id === $user->id;
    }
}
