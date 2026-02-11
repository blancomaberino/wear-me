<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModelImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'original_filename' => $this->original_filename,
            'is_primary' => $this->is_primary,
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
