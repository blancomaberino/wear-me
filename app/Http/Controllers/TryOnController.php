<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Http\Requests\StoreTryOnRequest;
use App\Http\Resources\GarmentSummaryResource;
use App\Http\Resources\ModelImageSummaryResource;
use App\Http\Resources\TryOnResultResource;
use App\Http\Resources\TryOnResultSummaryResource;
use App\Models\Lookbook;
use App\Models\TryOnResult;
use App\Services\TryOnService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryOnController extends Controller
{
    public function __construct(
        private TryOnService $tryOnService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

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
            'modelImages' => ModelImageSummaryResource::collection($user->modelImages()->get()),
            'garments' => GarmentSummaryResource::collection($user->garments()->get()),
            'sourceResult' => $sourceResult,
        ]);
    }

    public function store(StoreTryOnRequest $request)
    {
        $tryOnResult = $this->tryOnService->createTryOn($request->user(), $request);

        return redirect()->route('tryon.show', $tryOnResult)
            ->with('success', __('messages.tryon_started'));
    }

    public function show(Request $request, TryOnResult $tryOnResult)
    {
        $this->authorize('view', $tryOnResult);

        $tryOnResult->load(['modelImage', 'sourceResult', 'garment', 'garments']);

        return Inertia::render('TryOn/Result', [
            'tryOnResult' => new TryOnResultResource($tryOnResult),
            'lookbooks' => $request->user()->lookbooks()->withCount('items')->get(),
        ]);
    }

    public function status(Request $request, TryOnResult $tryOnResult)
    {
        $this->authorize('view', $tryOnResult);

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
            ->paginate(12);

        return Inertia::render('TryOn/History', [
            'results' => [
                'data' => TryOnResultSummaryResource::collection($paginator->items()),
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
        $this->authorize('update', $tryOnResult);

        $tryOnResult->update(['is_favorite' => !$tryOnResult->is_favorite]);

        return redirect()->back();
    }
}
