<?php

namespace App\Policies;

use App\Models\ShareLink;
use App\Models\User;

class ShareLinkPolicy
{
    public function view(User $user, ShareLink $shareLink): bool
    {
        return $shareLink->user_id === $user->id;
    }

    public function delete(User $user, ShareLink $shareLink): bool
    {
        return $shareLink->user_id === $user->id;
    }
}
