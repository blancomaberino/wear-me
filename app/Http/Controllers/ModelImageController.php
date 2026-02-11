<?php

namespace App\Http\Controllers;

use App\Models\ModelImage;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ModelImageController extends Controller
{
    public function __construct(
        private ImageProcessingService $imageService
    ) {}

    public function index(Request $request)
    {
        $images = $request->user()
            ->modelImages()
            ->latest()
            ->get()
            ->map(fn (ModelImage $image) => [
                'id' => $image->id,
                'url' => $image->url,
                'thumbnail_url' => $image->thumbnail_url,
                'original_filename' => $image->original_filename,
                'is_primary' => $image->is_primary,
                'width' => $image->width,
                'height' => $image->height,
                'created_at' => $image->created_at->diffForHumans(),
            ]);

        return Inertia::render('ModelImages/Index', [
            'images' => $images,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        if ($request->user()->modelImages()->count() >= 50) {
            return redirect()->back()->withErrors(['image' => 'Maximum of 50 photos allowed.']);
        }

        $data = $this->imageService->processAndStore(
            $request->file('image'),
            'model-images'
        );

        $request->user()->modelImages()->create($data);

        return redirect()->back()->with('success', __('messages.photo_uploaded'));
    }

    public function setPrimary(Request $request, ModelImage $modelImage)
    {
        if ($modelImage->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->user()->modelImages()->update(['is_primary' => false]);
        $modelImage->update(['is_primary' => true]);

        return redirect()->back()->with('success', __('messages.photo_primary_updated'));
    }

    public function destroy(Request $request, ModelImage $modelImage)
    {
        if ($modelImage->user_id !== $request->user()->id) {
            abort(403);
        }

        Storage::disk('public')->delete($modelImage->path);
        if ($modelImage->thumbnail_path) {
            Storage::disk('public')->delete($modelImage->thumbnail_path);
        }

        $modelImage->delete();

        return redirect()->back()->with('success', __('messages.photo_deleted'));
    }
}
