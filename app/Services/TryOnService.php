<?php

namespace App\Services;

use App\Enums\ProcessingStatus;
use App\Http\Requests\StoreTryOnRequest;
use App\Jobs\ProcessTryOn;
use App\Models\TryOnResult;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TryOnService
{
    /**
     * Create a new try-on from a validated request.
     */
    public function createTryOn(User $user, StoreTryOnRequest $request): TryOnResult
    {
        $sourceResult = null;
        $modelImageId = $request->model_image_id;

        // Handle chaining from a previous result
        if ($request->source_tryon_result_id) {
            $sourceResult = TryOnResult::where('id', $request->source_tryon_result_id)
                ->where('user_id', $user->id)
                ->where('status', ProcessingStatus::Completed)
                ->firstOrFail();

            if (!$sourceResult->result_path) {
                abort(422, 'Source result has no image.');
            }

            $modelImageId = $sourceResult->model_image_id;
        }

        if ($modelImageId) {
            $user->modelImages()->findOrFail($modelImageId);
        }

        // Verify ownership of all garments
        $garments = $user->garments()->whereIn('id', $request->garment_ids)->get();
        if ($garments->count() !== count($request->garment_ids)) {
            abort(404);
        }

        $promptHint = $request->prompt_hint ?? $this->buildPromptHint($garments);

        $tryOnResult = TryOnResult::create([
            'user_id' => $user->id,
            'model_image_id' => $modelImageId,
            'source_tryon_result_id' => $sourceResult?->id,
            'garment_id' => $garments->first()->id,
            'provider_task_id' => 'pending_' . Str::random(20),
            'status' => ProcessingStatus::Pending,
            'prompt_hint' => $promptHint,
        ]);

        // Attach garments to pivot table with sort order
        $pivotData = [];
        foreach ($request->garment_ids as $index => $garmentId) {
            $pivotData[$garmentId] = ['sort_order' => $index];
        }
        $tryOnResult->garments()->attach($pivotData);

        ProcessTryOn::dispatch($tryOnResult);

        return $tryOnResult;
    }

    /**
     * Resolve garments from pivot table, falling back to legacy garment_id.
     */
    public function resolveGarments(TryOnResult $result): Collection
    {
        $garments = $result->garments;
        if ($garments->isEmpty() && $result->garment_id) {
            $garments = collect([$result->garment]);
        }
        return $garments;
    }

    /**
     * Build prompt hint from garment categories.
     */
    public function buildPromptHint(Collection $garments): string
    {
        if ($garments->count() !== 1) {
            return '';
        }

        $categoryLabels = [
            'upper' => 'top/upper body clothing',
            'lower' => 'bottom/lower body clothing',
            'dress' => 'full body dress',
        ];

        return $categoryLabels[$garments->first()->category->value] ?? '';
    }
}
