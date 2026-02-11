<?php

namespace App\Services;

use App\Models\Lookbook;
use App\Models\ShareLink;
use App\Models\TryOnResult;
use App\Models\User;

class ShareService
{
    public function createShareLink(User $user, string $shareableType, int $shareableId, ?string $expiresIn = null): ShareLink
    {
        $morphClass = match ($shareableType) {
            'lookbook' => Lookbook::class,
            'tryon_result' => TryOnResult::class,
            default => throw new \InvalidArgumentException("Invalid shareable type: {$shareableType}"),
        };

        // Verify ownership
        $model = $morphClass::where('id', $shareableId)->where('user_id', $user->id)->firstOrFail();

        $expiresAt = match ($expiresIn) {
            '1_day' => now()->addDay(),
            '7_days' => now()->addDays(7),
            '30_days' => now()->addDays(30),
            default => null,
        };

        return $user->shareLinks()->create([
            'shareable_type' => $morphClass,
            'shareable_id' => $shareableId,
            'expires_at' => $expiresAt,
        ]);
    }

    public function resolveShareLink(string $token): ?ShareLink
    {
        $link = ShareLink::where('token', $token)->first();

        if (!$link || !$link->isValid()) {
            return null;
        }

        $link->increment('view_count');

        return $link;
    }

    public function addReaction(ShareLink $link, string $type, string $visitorHash): bool
    {
        // Check for existing reaction from this visitor
        $existing = $link->reactions()->where('visitor_hash', $visitorHash)->first();
        if ($existing) {
            return false;
        }

        $link->reactions()->create([
            'type' => $type,
            'visitor_hash' => $visitorHash,
            'created_at' => now(),
        ]);

        return true;
    }
}
