<?php

namespace App\Http\Controllers;

use App\Enums\GarmentCategory;
use App\Http\Requests\StoreGarmentRequest;
use App\Http\Requests\UpdateGarmentRequest;
use App\Http\Resources\GarmentResource;
use App\Models\Garment;
use App\Services\WardrobeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GarmentController extends Controller
{
    public function __construct(
        private WardrobeService $wardrobeService
    ) {}

    public function index(Request $request)
    {
        $query = $request->user()->garments()->latest();

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        return Inertia::render('Wardrobe/Index', [
            'garments' => GarmentResource::collection($query->get()),
            'currentCategory' => $request->category ?? 'all',
            'categories' => array_map(fn ($c) => $c->value, GarmentCategory::cases()),
        ]);
    }

    public function store(StoreGarmentRequest $request)
    {
        if ($request->user()->garments()->count() >= 200) {
            return redirect()->back()->withErrors(['image' => 'Maximum of 200 garments allowed.']);
        }

        $this->wardrobeService->storeGarment(
            $request->user(),
            $request->only([
                'category', 'name', 'description', 'size_label', 'brand', 'material',
                'measurement_chest_cm', 'measurement_length_cm', 'measurement_waist_cm',
                'measurement_inseam_cm', 'measurement_shoulder_cm', 'measurement_sleeve_cm',
            ]),
            $request->file('image')
        );

        return redirect()->back()->with('success', __('messages.garment_uploaded'));
    }

    public function update(UpdateGarmentRequest $request, Garment $garment)
    {
        $this->authorize('update', $garment);

        $garment->update($request->only([
            'name', 'description', 'category', 'size_label', 'brand', 'material',
            'measurement_chest_cm', 'measurement_length_cm', 'measurement_waist_cm',
            'measurement_inseam_cm', 'measurement_shoulder_cm', 'measurement_sleeve_cm',
        ]));

        return redirect()->back()->with('success', __('messages.garment_updated'));
    }

    public function destroy(Request $request, Garment $garment)
    {
        $this->authorize('delete', $garment);

        $this->wardrobeService->deleteGarment($garment);

        return redirect()->back()->with('success', __('messages.garment_deleted'));
    }
}
