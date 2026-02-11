<?php

namespace App\Policies;

use App\Models\PackingList;
use App\Models\User;

class PackingListPolicy
{
    public function view(User $user, PackingList $packingList): bool
    {
        return $packingList->user_id === $user->id;
    }

    public function update(User $user, PackingList $packingList): bool
    {
        return $packingList->user_id === $user->id;
    }

    public function delete(User $user, PackingList $packingList): bool
    {
        return $packingList->user_id === $user->id;
    }
}
