<?php

namespace App\Policies;

use App\Models\TryOnVideo;
use App\Models\User;

class TryOnVideoPolicy
{
    public function view(User $user, TryOnVideo $tryOnVideo): bool
    {
        return $tryOnVideo->user_id === $user->id;
    }

    public function update(User $user, TryOnVideo $tryOnVideo): bool
    {
        return $tryOnVideo->user_id === $user->id;
    }

    public function delete(User $user, TryOnVideo $tryOnVideo): bool
    {
        return $tryOnVideo->user_id === $user->id;
    }
}
