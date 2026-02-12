<?php

namespace App\Services;

use App\Models\Lookbook;
use App\Models\LookbookItem;
use App\Models\User;

class LookbookService
{
    public function createLookbook(User $user, array $data): Lookbook
    {
        return $user->lookbooks()->create($data);
    }

    public function addItem(Lookbook $lookbook, string $itemableType, int $itemableId, ?string $note = null): ?LookbookItem
    {
        $morphClass = match ($itemableType) {
            'tryon_result' => \App\Models\TryOnResult::class,
            'outfit_suggestion' => \App\Models\OutfitSuggestion::class,
            default => throw new \InvalidArgumentException("Invalid itemable type: {$itemableType}"),
        };

        // Verify the referenced item belongs to the lookbook owner (prevent cross-tenant reference)
        $morphClass::where('id', $itemableId)
            ->where('user_id', $lookbook->user_id)
            ->firstOrFail();

        // Check for duplicate
        $existing = $lookbook->items()
            ->where('itemable_type', $morphClass)
            ->where('itemable_id', $itemableId)
            ->first();

        if ($existing) {
            return null;
        }

        $maxOrder = $lookbook->items()->max('sort_order') ?? -1;

        return $lookbook->items()->create([
            'itemable_type' => $morphClass,
            'itemable_id' => $itemableId,
            'note' => $note,
            'sort_order' => $maxOrder + 1,
        ]);
    }

    public function reorderItems(Lookbook $lookbook, array $itemIds): void
    {
        // Validate all IDs belong to this lookbook
        $validIds = $lookbook->items()->pluck('id')->toArray();
        $invalidIds = array_diff($itemIds, $validIds);
        if (!empty($invalidIds)) {
            throw new \InvalidArgumentException('Invalid item IDs provided.');
        }

        foreach ($itemIds as $index => $itemId) {
            $lookbook->items()->where('id', $itemId)->update(['sort_order' => $index]);
        }
    }
}
