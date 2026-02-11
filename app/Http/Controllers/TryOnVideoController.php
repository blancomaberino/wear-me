<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Http\Requests\StoreTryOnVideoRequest;
use App\Http\Resources\TryOnVideoResource;
use App\Http\Resources\TryOnVideoSummaryResource;
use App\Jobs\ProcessTryOnVideo;
use App\Models\TryOnVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TryOnVideoController extends Controller
{
    public function index(Request $request)
    {
        $tryOnResults = $request->user()->tryonResults()
            ->where('status', ProcessingStatus::Completed)
            ->with(['modelImage', 'garment'])
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'result_url' => $r->result_url,
                'model_image' => ['thumbnail_url' => $r->modelImage->thumbnail_url],
                'garment' => [
                    'name' => $r->garment->name ?? $r->garment->original_filename,
                    'thumbnail_url' => $r->garment->thumbnail_url,
                ],
            ]);

        return Inertia::render('Videos/Index', [
            'tryOnResults' => $tryOnResults,
        ]);
    }

    public function store(StoreTryOnVideoRequest $request)
    {
        $tryOnResult = $request->user()->tryonResults()->findOrFail($request->tryon_result_id);

        $video = $request->user()->tryonVideos()->create([
            'tryon_result_id' => $tryOnResult->id,
            'model_image_id' => $tryOnResult->model_image_id,
            'garment_id' => $tryOnResult->garment_id,
            'kling_task_id' => 'pending_' . Str::random(20),
            'status' => ProcessingStatus::Pending,
        ]);

        ProcessTryOnVideo::dispatch($video);

        return redirect()->route('videos.show', $video)
            ->with('success', __('messages.video_started'));
    }

    public function show(Request $request, TryOnVideo $video)
    {
        $this->authorize('view', $video);

        $video->load(['modelImage', 'garment', 'tryonResult']);

        return Inertia::render('Videos/Show', [
            'video' => new TryOnVideoResource($video),
        ]);
    }

    public function status(Request $request, TryOnVideo $video)
    {
        $this->authorize('view', $video);

        return response()->json([
            'status' => $video->status->value,
            'video_url' => $video->video_url,
            'error_message' => $video->error_message,
        ]);
    }

    public function history(Request $request)
    {
        $videos = $request->user()
            ->tryonVideos()
            ->with(['modelImage', 'garment'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Videos/History', [
            'videos' => TryOnVideoSummaryResource::collection($videos),
        ]);
    }
}
