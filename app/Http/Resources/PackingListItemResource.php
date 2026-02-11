<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackingListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'garment' => new GarmentSummaryResource($this->whenLoaded('garment')),
            'day_number' => $this->day_number,
            'occasion' => $this->occasion,
            'is_packed' => $this->is_packed,
        ];
    }
}
