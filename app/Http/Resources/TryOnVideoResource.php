<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TryOnVideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'video_url' => $this->video_url,
            'duration_seconds' => $this->duration_seconds,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at->diffForHumans(),
            'model_image' => ['thumbnail_url' => $this->modelImage->thumbnail_url],
            'garment' => [
                'name' => $this->garment->name ?? $this->garment->original_filename,
                'thumbnail_url' => $this->garment->thumbnail_url,
            ],
        ];
    }
}
