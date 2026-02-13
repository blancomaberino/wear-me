<?php

namespace App\Policies;

use App\Models\Lookbook;
use App\Models\User;

class LookbookPolicy
{
    public function view(User $user, Lookbook $lookbook): bool
    {
        return $lookbook->user_id === $user->id;
    }

    public function update(User $user, Lookbook $lookbook): bool
    {
        return $lookbook->user_id === $user->id;
    }

    public function delete(User $user, Lookbook $lookbook): bool
    {
        return $lookbook->user_id === $user->id;
    }
}
