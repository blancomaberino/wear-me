<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Jobs\ProcessTryOnVideo;
use App\Models\TryOnVideo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryOnVideoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tryOnResults = $user->tryonResults()
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

    public function store(Request $request)
    {
        $request->validate([
            'tryon_result_id' => 'required|exists:tryon_results,id',
        ]);

        $tryOnResult = $request->user()->tryonResults()->findOrFail($request->tryon_result_id);

        $video = TryOnVideo::create([
            'user_id' => $request->user()->id,
            'tryon_result_id' => $tryOnResult->id,
            'model_image_id' => $tryOnResult->model_image_id,
            'garment_id' => $tryOnResult->garment_id,
            'kling_task_id' => 'pending_' . uniqid(),
            'status' => ProcessingStatus::Pending,
        ]);

        ProcessTryOnVideo::dispatch($video);

        return redirect()->route('videos.show', $video)
            ->with('success', __('messages.video_started'));
    }

    public function show(Request $request, TryOnVideo $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $video->load(['modelImage', 'garment', 'tryonResult']);

        return Inertia::render('Videos/Show', [
            'video' => [
                'id' => $video->id,
                'status' => $video->status->value,
                'video_url' => $video->video_url,
                'duration_seconds' => $video->duration_seconds,
                'error_message' => $video->error_message,
                'created_at' => $video->created_at->diffForHumans(),
                'model_image' => ['thumbnail_url' => $video->modelImage->thumbnail_url],
                'garment' => [
                    'name' => $video->garment->name ?? $video->garment->original_filename,
                    'thumbnail_url' => $video->garment->thumbnail_url,
                ],
            ],
        ]);
    }

    public function status(Request $request, TryOnVideo $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

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
            ->paginate(12)
            ->through(fn ($v) => [
                'id' => $v->id,
                'status' => $v->status->value,
                'video_url' => $v->video_url,
                'duration_seconds' => $v->duration_seconds,
                'created_at' => $v->created_at->diffForHumans(),
                'garment' => [
                    'name' => $v->garment->name ?? $v->garment->original_filename,
                    'thumbnail_url' => $v->garment->thumbnail_url,
                ],
            ]);

        return Inertia::render('Videos/History', [
            'videos' => $videos,
        ]);
    }
}
