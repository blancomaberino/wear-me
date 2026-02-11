<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GarmentSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? $this->original_filename,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'category' => $this->category->value,
            'size_label' => $this->size_label,
            'brand' => $this->brand,
        ];
    }
}
