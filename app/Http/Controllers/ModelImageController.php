<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModelImageRequest;
use App\Http\Resources\ModelImageResource;
use App\Models\ModelImage;
use App\Services\WardrobeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModelImageController extends Controller
{
    public function __construct(
        private WardrobeService $wardrobeService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('ModelImages/Index', [
            'images' => ModelImageResource::collection(
                $request->user()->modelImages()->latest()->get()
            ),
        ]);
    }

    public function store(StoreModelImageRequest $request)
    {
        if ($request->user()->modelImages()->count() >= 50) {
            return redirect()->back()->withErrors(['image' => 'Maximum of 50 photos allowed.']);
        }

        $this->wardrobeService->storeModelImage($request->user(), $request->file('image'));

        return redirect()->back()->with('success', __('messages.photo_uploaded'));
    }

    public function setPrimary(Request $request, ModelImage $modelImage)
    {
        $this->authorize('update', $modelImage);

        $request->user()->modelImages()->update(['is_primary' => false]);
        $modelImage->update(['is_primary' => true]);

        return redirect()->back()->with('success', __('messages.photo_primary_updated'));
    }

    public function destroy(Request $request, ModelImage $modelImage)
    {
        $this->authorize('delete', $modelImage);

        $this->wardrobeService->deleteModelImage($modelImage);

        return redirect()->back()->with('success', __('messages.photo_deleted'));
    }
}
