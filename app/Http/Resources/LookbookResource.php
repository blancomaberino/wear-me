<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LookbookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_image_url' => $this->cover_image_url,
            'is_public' => $this->is_public,
            'slug' => $this->slug,
            'items_count' => $this->items_count ?? $this->items()->count(),
            'items' => LookbookItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
