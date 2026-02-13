<?php

namespace App\Http\Controllers;

use App\Enums\GarmentCategory;
use App\Http\Requests\BulkStoreGarmentRequest;
use App\Http\Requests\StoreGarmentRequest;
use App\Http\Requests\UpdateGarmentRequest;
use App\Http\Resources\GarmentResource;
use App\Jobs\BackfillGarmentColors;
use App\Jobs\ProcessBulkGarment;
use App\Models\Garment;
use App\Models\User;
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
            $category = GarmentCategory::tryFrom($request->category);
            if ($category) {
                $query->where('category', $category);
            }
        }

        return Inertia::render('Wardrobe/Index', [
            'garments' => GarmentResource::collection($query->get()),
            'currentCategory' => $request->category ?? 'all',
            'categories' => array_map(fn ($c) => $c->value, GarmentCategory::cases()),
        ]);
    }

    public function store(StoreGarmentRequest $request)
    {
        if ($request->user()->garments()->count() >= User::MAX_GARMENTS) {
            return redirect()->back()->withErrors(['image' => __('messages.garmentLimitReached')]);
        }

        $this->wardrobeService->storeGarment(
            $request->user(),
            $request->only([
                'category', 'name', 'description', 'clothing_type', 'size_label', 'brand', 'material',
                'measurement_chest_cm', 'measurement_length_cm', 'measurement_waist_cm',
                'measurement_inseam_cm', 'measurement_shoulder_cm', 'measurement_sleeve_cm',
            ]),
            $request->file('image')
        );

        return redirect()->back()->with('success', __('messages.garment_uploaded'));
    }

    public function bulkStore(BulkStoreGarmentRequest $request)
    {
        $user = $request->user();
        $currentCount = $user->garments()->count();
        $files = $request->file('images');
        $maxAllowed = User::MAX_GARMENTS - $currentCount;

        if ($maxAllowed <= 0) {
            return redirect()->back()->withErrors(['images' => __('messages.garmentLimitReached')]);
        }

        $filesToProcess = array_slice($files, 0, $maxAllowed);

        foreach ($filesToProcess as $file) {
            $tempPath = $file->store('temp/bulk-uploads', 'local');
            ProcessBulkGarment::dispatch(
                $user,
                $tempPath,
                $file->getClientOriginalName(),
                $request->input('category')
            );
        }

        return redirect()->back()->with('success', __('messages.bulk_upload_started', ['count' => count($filesToProcess)]));
    }

    public function update(UpdateGarmentRequest $request, Garment $garment)
    {
        $this->authorize('update', $garment);

        $garment->update($request->only([
            'name', 'description', 'category', 'clothing_type', 'size_label', 'brand', 'material',
            'measurement_chest_cm', 'measurement_length_cm', 'measurement_waist_cm',
            'measurement_inseam_cm', 'measurement_shoulder_cm', 'measurement_sleeve_cm',
            'color_tags',
        ]));

        return redirect()->back()->with('success', __('messages.garment_updated'));
    }

    public function destroy(Request $request, Garment $garment)
    {
        $this->authorize('delete', $garment);

        $this->wardrobeService->deleteGarment($garment);

        return redirect()->back()->with('success', __('messages.garment_deleted'));
    }

    public function backfillColors(Request $request)
    {
        $user = $request->user();

        $pendingCount = $user->garments()
            ->missingColorTags()
            ->count();

        if ($pendingCount === 0) {
            return redirect()->back()->with('success', __('messages.no_garments_to_backfill'));
        }

        BackfillGarmentColors::dispatch($user);

        return redirect()->back()->with('success', __('messages.color_backfill_started', ['count' => $pendingCount]));
    }
}
