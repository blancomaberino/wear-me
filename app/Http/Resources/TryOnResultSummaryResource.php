<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TryOnResultSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $garments = $this->resolveGarments();

        $combinedName = $garments
            ->map(fn ($g) => $g->name ?? $g->original_filename)
            ->join(' + ');

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'result_url' => $this->result_url,
            'is_favorite' => $this->is_favorite,
            'created_at' => $this->created_at->diffForHumans(),
            'model_image' => [
                'thumbnail_url' => $this->modelImage?->thumbnail_url,
            ],
            'garment' => [
                'name' => $combinedName,
                'thumbnail_url' => $garments->first()?->thumbnail_url,
                'category' => $garments->first()?->category?->value,
            ],
            'garments' => $garments->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name ?? $g->original_filename,
                'thumbnail_url' => $g->thumbnail_url,
                'category' => $g->category->value,
            ])->values()->all(),
        ];
    }

    protected function resolveGarments()
    {
        $garments = $this->whenLoaded('garments', fn () => $this->garments, collect());

        if ($garments->isEmpty() && $this->whenLoaded('garment', fn () => $this->garment)) {
            $garments = collect([$this->garment]);
        }

        return $garments;
    }
}
