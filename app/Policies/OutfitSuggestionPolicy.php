<?php

namespace App\Policies;

use App\Models\OutfitSuggestion;
use App\Models\User;

class OutfitSuggestionPolicy
{
    public function view(User $user, OutfitSuggestion $suggestion): bool
    {
        return $suggestion->user_id === $user->id;
    }

    public function update(User $user, OutfitSuggestion $suggestion): bool
    {
        return $suggestion->user_id === $user->id;
    }

    public function delete(User $user, OutfitSuggestion $suggestion): bool
    {
        return $suggestion->user_id === $user->id;
    }
}
