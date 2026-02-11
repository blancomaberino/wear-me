<?php

namespace App\Policies;

use App\Models\TryOnResult;
use App\Models\User;

class TryOnResultPolicy
{
    public function view(User $user, TryOnResult $tryOnResult): bool
    {
        return $tryOnResult->user_id === $user->id;
    }

    public function update(User $user, TryOnResult $tryOnResult): bool
    {
        return $tryOnResult->user_id === $user->id;
    }
}
