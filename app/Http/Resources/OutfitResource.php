<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutfitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'occasion' => $this->occasion,
            'notes' => $this->notes,
            'harmony_score' => $this->harmony_score,
            'template' => $this->whenLoaded('template', fn () => $this->template ? new OutfitTemplateResource($this->template) : null),
            'garments' => GarmentSummaryResource::collection($this->whenLoaded('garments')),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
