<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TryOnVideoSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'video_url' => $this->video_url,
            'duration_seconds' => $this->duration_seconds,
            'created_at' => $this->created_at->diffForHumans(),
            'garment' => [
                'name' => $this->garment->name ?? $this->garment->original_filename,
                'thumbnail_url' => $this->garment->thumbnail_url,
            ],
        ];
    }
}
