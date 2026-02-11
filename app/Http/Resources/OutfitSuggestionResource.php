<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutfitSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'garment_ids' => $this->garment_ids,
            'garments' => $this->resolveGarments()->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name ?? $g->original_filename,
                'thumbnail_url' => $g->thumbnail_url,
                'category' => $g->category->value,
            ]),
            'suggestion_text' => $this->suggestion_text,
            'occasion' => $this->occasion,
            'is_saved' => $this->is_saved,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
