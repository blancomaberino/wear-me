<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Jobs\ProcessTryOn;
use App\Models\TryOnResult;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryOnController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $modelImages = $user->modelImages()->get()->map(fn ($img) => [
            'id' => $img->id,
            'url' => $img->url,
            'thumbnail_url' => $img->thumbnail_url,
            'original_filename' => $img->original_filename,
            'is_primary' => $img->is_primary,
        ]);

        $garments = $user->garments()->get()->map(fn ($g) => [
            'id' => $g->id,
            'url' => $g->url,
            'thumbnail_url' => $g->thumbnail_url,
            'name' => $g->name ?? $g->original_filename,
            'category' => $g->category->value,
            'size_label' => $g->size_label,
            'brand' => $g->brand,
        ]);

        $sourceResult = null;
        if ($sourceResultId = $request->query('source_result')) {
            $result = TryOnResult::where('id', $sourceResultId)
                ->where('user_id', $user->id)
                ->where('status', ProcessingStatus::Completed)
                ->first();

            if ($result && $result->result_path) {
                $sourceResult = [
                    'id' => $result->id,
                    'result_url' => $result->result_url,
                ];
            }
        }

        return Inertia::render('TryOn/Index', [
            'modelImages' => $modelImages,
            'garments' => $garments,
            'sourceResult' => $sourceResult,
        ]);
    }

    public function store(Request $request)
    {
        // Backward compat: if old client sends garment_id instead of garment_ids
        if ($request->has('garment_id') && !$request->has('garment_ids')) {
            $request->merge(['garment_ids' => [$request->garment_id]]);
        }

        $request->validate([
            'model_image_id' => 'required_without:source_tryon_result_id|nullable|exists:model_images,id',
            'source_tryon_result_id' => 'nullable|exists:tryon_results,id',
            'garment_ids' => 'required|array|min:1|max:5',
            'garment_ids.*' => 'required|exists:garments,id',
            'prompt_hint' => 'nullable|string|max:200',
        ]);

        $user = $request->user();
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

            // Preserve chain origin model_image_id
            $modelImageId = $sourceResult->model_image_id;
        }

        if ($modelImageId) {
            $modelImage = $user->modelImages()->findOrFail($modelImageId);
        }

        // Verify ownership of all garments
        $garments = $user->garments()->whereIn('id', $request->garment_ids)->get();
        if ($garments->count() !== count($request->garment_ids)) {
            abort(404);
        }

        $promptHint = $request->prompt_hint;
        if (empty($promptHint) && $garments->count() === 1) {
            $categoryLabels = [
                'upper' => 'top/upper body clothing',
                'lower' => 'bottom/lower body clothing',
                'dress' => 'full body dress',
            ];
            $promptHint = $categoryLabels[$garments->first()->category->value] ?? '';
        }

        $tryOnResult = TryOnResult::create([
            'user_id' => $user->id,
            'model_image_id' => $modelImageId,
            'source_tryon_result_id' => $sourceResult?->id,
            'garment_id' => $garments->first()->id,
            'provider_task_id' => 'pending_' . uniqid(),
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

        return redirect()->route('tryon.show', $tryOnResult)
            ->with('success', __('messages.tryon_started'));
    }

    public function show(Request $request, TryOnResult $tryOnResult)
    {
        if ($tryOnResult->user_id !== $request->user()->id) {
            abort(403);
        }

        $tryOnResult->load(['modelImage', 'sourceResult', 'garment', 'garments']);

        $garmentsArray = $tryOnResult->garments->map(fn ($g) => [
            'id' => $g->id,
            'name' => $g->name ?? $g->original_filename,
            'url' => $g->url,
            'thumbnail_url' => $g->thumbnail_url,
            'category' => $g->category->value,
        ])->values()->all();

        // Fallback to legacy single garment if pivot is empty
        if (empty($garmentsArray) && $tryOnResult->garment) {
            $garmentsArray = [[
                'id' => $tryOnResult->garment->id,
                'name' => $tryOnResult->garment->name ?? $tryOnResult->garment->original_filename,
                'url' => $tryOnResult->garment->url,
                'thumbnail_url' => $tryOnResult->garment->thumbnail_url,
                'category' => $tryOnResult->garment->category->value,
            ]];
        }

        $firstGarment = $garmentsArray[0] ?? null;

        return Inertia::render('TryOn/Result', [
            'tryOnResult' => [
                'id' => $tryOnResult->id,
                'status' => $tryOnResult->status->value,
                'result_url' => $tryOnResult->result_url,
                'error_message' => $tryOnResult->error_message,
                'is_favorite' => $tryOnResult->is_favorite,
                'created_at' => $tryOnResult->created_at->diffForHumans(),
                'model_image' => [
                    'url' => $tryOnResult->source_tryon_result_id
                        ? $tryOnResult->sourceResult->result_url
                        : $tryOnResult->modelImage->url,
                    'thumbnail_url' => $tryOnResult->source_tryon_result_id
                        ? $tryOnResult->sourceResult->result_url
                        : $tryOnResult->modelImage->thumbnail_url,
                ],
                'garment' => $firstGarment ? [
                    'name' => $firstGarment['name'],
                    'url' => $firstGarment['url'],
                    'thumbnail_url' => $firstGarment['thumbnail_url'],
                    'category' => $firstGarment['category'],
                ] : null,
                'garments' => $garmentsArray,
            ],
        ]);
    }

    public function status(Request $request, TryOnResult $tryOnResult)
    {
        if ($tryOnResult->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'status' => $tryOnResult->status->value,
            'result_url' => $tryOnResult->result_url,
            'error_message' => $tryOnResult->error_message,
        ]);
    }

    public function history(Request $request)
    {
        $paginator = $request->user()
            ->tryonResults()
            ->with(['modelImage', 'garment', 'garments'])
            ->latest()
            ->paginate(12)
            ->through(function ($result) {
                $garmentsList = $result->garments;
                if ($garmentsList->isEmpty() && $result->garment) {
                    $garmentsList = collect([$result->garment]);
                }

                $combinedName = $garmentsList
                    ->map(fn ($g) => $g->name ?? $g->original_filename)
                    ->join(' + ');

                $garmentsArray = $garmentsList->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name ?? $g->original_filename,
                    'thumbnail_url' => $g->thumbnail_url,
                    'category' => $g->category->value,
                ])->values()->all();

                return [
                    'id' => $result->id,
                    'status' => $result->status->value,
                    'result_url' => $result->result_url,
                    'is_favorite' => $result->is_favorite,
                    'created_at' => $result->created_at->diffForHumans(),
                    'model_image' => [
                        'thumbnail_url' => $result->modelImage->thumbnail_url,
                    ],
                    'garment' => [
                        'name' => $combinedName,
                        'thumbnail_url' => $garmentsList->first()?->thumbnail_url,
                    ],
                    'garments' => $garmentsArray,
                ];
            });

        return Inertia::render('TryOn/History', [
            'results' => [
                'data' => $paginator->items(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function toggleFavorite(Request $request, TryOnResult $tryOnResult)
    {
        if ($tryOnResult->user_id !== $request->user()->id) {
            abort(403);
        }

        $tryOnResult->update(['is_favorite' => !$tryOnResult->is_favorite]);

        return redirect()->back();
    }
}
