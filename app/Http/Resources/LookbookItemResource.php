<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LookbookItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'itemable_type' => class_basename($this->itemable_type),
            'itemable_id' => $this->itemable_id,
            'note' => $this->note,
            'sort_order' => $this->sort_order,
            'item' => $this->resolveItemResource(),
        ];
    }

    private function resolveItemResource(): ?array
    {
        $item = $this->itemable;
        if (!$item) return null;

        if ($item instanceof \App\Models\TryOnResult) {
            return (new TryOnResultSummaryResource($item))->resolve();
        }

        if ($item instanceof \App\Models\OutfitSuggestion) {
            return (new OutfitSuggestionResource($item))->resolve();
        }

        return null;
    }
}
