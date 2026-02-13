<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TryOnResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $garments = $this->resolveGarments();
        $firstGarment = $garments->first();

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'result_url' => $this->result_url,
            'error_message' => $this->error_message,
            'is_favorite' => $this->is_favorite,
            'created_at' => $this->created_at->diffForHumans(),
            'model_image' => [
                'url' => $this->source_tryon_result_id
                    ? $this->sourceResult?->result_url
                    : $this->modelImage?->url,
                'thumbnail_url' => $this->source_tryon_result_id
                    ? $this->sourceResult?->result_url
                    : $this->modelImage?->thumbnail_url,
            ],
            'garment' => $firstGarment ? [
                'name' => $firstGarment->name ?? $firstGarment->original_filename,
                'url' => $firstGarment->url,
                'thumbnail_url' => $firstGarment->thumbnail_url,
                'category' => $firstGarment->category->value,
            ] : null,
            'garments' => $garments->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name ?? $g->original_filename,
                'url' => $g->url,
                'thumbnail_url' => $g->thumbnail_url,
                'category' => $g->category->value,
            ])->values()->all(),
        ];
    }

    /**
     * Resolve garments from pivot table, falling back to legacy garment_id.
     */
    protected function resolveGarments()
    {
        $garments = $this->whenLoaded('garments', fn () => $this->garments, collect());

        if ($garments->isEmpty() && $this->whenLoaded('garment', fn () => $this->garment)) {
            $garments = collect([$this->garment]);
        }

        return $garments;
    }
}
