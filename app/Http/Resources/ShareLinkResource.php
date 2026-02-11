<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'url' => $this->url,
            'shareable_type' => class_basename($this->shareable_type),
            'shareable_id' => $this->shareable_id,
            'expires_at' => $this->expires_at?->toISOString(),
            'is_active' => $this->is_active,
            'view_count' => $this->view_count,
            'reactions_summary' => $this->reactions_summary,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
