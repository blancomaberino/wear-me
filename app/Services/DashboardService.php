<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getRecentTryOns(User $user, int $limit = 6): Collection
    {
        return $user->tryonResults()
            ->with(['modelImage', 'garment', 'garments'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getWardrobeStats(User $user): array
    {
        return [
            'total' => $user->garments()->count(),
            'upper' => $user->garments()->where('category', 'upper')->count(),
            'lower' => $user->garments()->where('category', 'lower')->count(),
            'dress' => $user->garments()->where('category', 'dress')->count(),
        ];
    }

    public function getCounts(User $user): array
    {
        return [
            'modelImageCount' => $user->modelImages()->count(),
            'videoCount' => $user->tryonVideos()->count(),
            'savedSuggestionCount' => $user->outfitSuggestions()->where('is_saved', true)->count(),
        ];
    }
}
