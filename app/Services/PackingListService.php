<?php

namespace App\Services;

use App\Models\PackingList;
use App\Models\User;

class PackingListService
{
    public function createPackingList(User $user, array $data): PackingList
    {
        return $user->packingLists()->create($data);
    }

    public function addItem(PackingList $packingList, int $garmentId, ?int $dayNumber = null, ?string $occasion = null): void
    {
        $packingList->items()->create([
            'garment_id' => $garmentId,
            'day_number' => $dayNumber,
            'occasion' => $occasion,
        ]);
    }

    public function togglePacked(PackingList $packingList, int $itemId): bool
    {
        $item = $packingList->items()->findOrFail($itemId);
        $item->update(['is_packed' => !$item->is_packed]);
        return $item->fresh()->is_packed;
    }
}
