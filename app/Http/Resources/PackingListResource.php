<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackingListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'destination' => $this->destination,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'occasions' => $this->occasions,
            'notes' => $this->notes,
            'items' => PackingListItemResource::collection($this->whenLoaded('items')),
            'packed_count' => $this->packed_count,
            'total_count' => $this->total_count,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
