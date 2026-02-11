<?php

namespace App\Http\Controllers;

use App\Enums\GarmentCategory;
use App\Models\Garment;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GarmentController extends Controller
{
    public function __construct(
        private ImageProcessingService $imageService
    ) {}

    public function index(Request $request)
    {
        $query = $request->user()->garments()->latest();

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $garments = $query->get()->map(fn (Garment $garment) => [
            'id' => $garment->id,
            'url' => $garment->url,
            'thumbnail_url' => $garment->thumbnail_url,
            'original_filename' => $garment->original_filename,
            'name' => $garment->name,
            'category' => $garment->category->value,
            'description' => $garment->description,
            'color_tags' => $garment->color_tags,
            'size_label' => $garment->size_label,
            'brand' => $garment->brand,
            'material' => $garment->material,
            'measurement_chest_cm' => $garment->measurement_chest_cm,
            'measurement_length_cm' => $garment->measurement_length_cm,
            'measurement_waist_cm' => $garment->measurement_waist_cm,
            'measurement_inseam_cm' => $garment->measurement_inseam_cm,
            'measurement_shoulder_cm' => $garment->measurement_shoulder_cm,
            'measurement_sleeve_cm' => $garment->measurement_sleeve_cm,
            'created_at' => $garment->created_at->diffForHumans(),
        ]);

        return Inertia::render('Wardrobe/Index', [
            'garments' => $garments,
            'currentCategory' => $request->category ?? 'all',
            'categories' => array_map(fn ($c) => $c->value, GarmentCategory::cases()),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'category' => ['required', Rule::enum(GarmentCategory::class)],
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'size_label' => 'nullable|string|max:20',
            'brand' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'measurement_chest_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_length_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_waist_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_inseam_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_shoulder_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_sleeve_cm' => 'nullable|numeric|min:0|max:300',
        ]);

        $data = $this->imageService->processAndStore(
            $request->file('image'),
            'garments'
        );

        $request->user()->garments()->create(array_merge($data, [
            'category' => $request->category,
            'name' => $request->name,
            'description' => $request->description,
            'size_label' => $request->size_label,
            'brand' => $request->brand,
            'material' => $request->material,
            'measurement_chest_cm' => $request->measurement_chest_cm,
            'measurement_length_cm' => $request->measurement_length_cm,
            'measurement_waist_cm' => $request->measurement_waist_cm,
            'measurement_inseam_cm' => $request->measurement_inseam_cm,
            'measurement_shoulder_cm' => $request->measurement_shoulder_cm,
            'measurement_sleeve_cm' => $request->measurement_sleeve_cm,
        ]));

        return redirect()->back()->with('success', __('messages.garment_uploaded'));
    }

    public function update(Request $request, Garment $garment)
    {
        if ($garment->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => ['nullable', Rule::enum(GarmentCategory::class)],
            'size_label' => 'nullable|string|max:20',
            'brand' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'measurement_chest_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_length_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_waist_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_inseam_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_shoulder_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_sleeve_cm' => 'nullable|numeric|min:0|max:300',
        ]);

        $garment->update($request->only([
            'name', 'description', 'category', 'size_label', 'brand', 'material',
            'measurement_chest_cm', 'measurement_length_cm', 'measurement_waist_cm',
            'measurement_inseam_cm', 'measurement_shoulder_cm', 'measurement_sleeve_cm',
        ]));

        return redirect()->back()->with('success', __('messages.garment_updated'));
    }

    public function destroy(Request $request, Garment $garment)
    {
        if ($garment->user_id !== $request->user()->id) {
            abort(403);
        }

        Storage::disk('public')->delete($garment->path);
        if ($garment->thumbnail_path) {
            Storage::disk('public')->delete($garment->thumbnail_path);
        }

        $garment->delete();

        return redirect()->back()->with('success', __('messages.garment_deleted'));
    }
}
