<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutfitRequest;
use App\Http\Resources\OutfitResource;
use App\Http\Resources\OutfitTemplateResource;
use App\Http\Resources\GarmentResource;
use App\Models\Outfit;
use App\Models\OutfitTemplate;
use App\Services\OutfitService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OutfitController extends Controller
{
    public function __construct(
        private OutfitService $outfitService
    ) {}

    public function index(Request $request)
    {
        $outfits = $request->user()
            ->outfits()
            ->with(['template', 'garments'])
            ->latest()
            ->get();

        return Inertia::render('Outfits/MyOutfits', [
            'outfits' => OutfitResource::collection($outfits),
        ]);
    }

    public function store(StoreOutfitRequest $request)
    {
        $this->outfitService->createOutfit(
            $request->user(),
            $request->only(['name', 'occasion', 'notes', 'outfit_template_id']),
            $request->input('garments')
        );

        return redirect()->route('my-outfits.index')->with('success', __('messages.outfit_created'));
    }

    public function show(Request $request, Outfit $outfit)
    {
        $this->authorize('view', $outfit);

        $outfit->load(['template', 'garments']);

        // Also load garments for the builder
        $garments = $request->user()->garments()->latest()->get();

        return Inertia::render('Outfits/ShowOutfit', [
            'outfit' => new OutfitResource($outfit),
            'garments' => GarmentResource::collection($garments),
        ]);
    }

    public function destroy(Request $request, Outfit $outfit)
    {
        $this->authorize('delete', $outfit);

        $outfit->delete();

        return redirect()->route('my-outfits.index')->with('success', __('messages.outfit_deleted'));
    }
}
