<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GarmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'original_filename' => $this->original_filename,
            'name' => $this->name,
            'category' => $this->category->value,
            'description' => $this->description,
            'color_tags' => $this->color_tags,
            'size_label' => $this->size_label,
            'brand' => $this->brand,
            'material' => $this->material,
            'measurement_chest_cm' => $this->measurement_chest_cm,
            'measurement_length_cm' => $this->measurement_length_cm,
            'measurement_waist_cm' => $this->measurement_waist_cm,
            'measurement_inseam_cm' => $this->measurement_inseam_cm,
            'measurement_shoulder_cm' => $this->measurement_shoulder_cm,
            'measurement_sleeve_cm' => $this->measurement_sleeve_cm,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
