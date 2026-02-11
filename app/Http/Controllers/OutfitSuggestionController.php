<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateOutfitRequest;
use App\Http\Resources\OutfitSuggestionResource;
use App\Jobs\GenerateOutfitSuggestion;
use App\Models\OutfitSuggestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OutfitSuggestionController extends Controller
{
    public function index(Request $request)
    {
        $suggestions = $request->user()
            ->outfitSuggestions()
            ->latest()
            ->paginate(10);

        return Inertia::render('Outfits/Suggestions', [
            'suggestions' => OutfitSuggestionResource::collection($suggestions),
            'garmentCount' => $request->user()->garments()->count(),
        ]);
    }

    public function generate(GenerateOutfitRequest $request)
    {
        GenerateOutfitSuggestion::dispatch($request->user(), $request->occasion);

        return redirect()->back()->with('success', __('messages.outfit_generating'));
    }

    public function toggleSaved(Request $request, OutfitSuggestion $suggestion)
    {
        $this->authorize('update', $suggestion);

        $suggestion->update(['is_saved' => !$suggestion->is_saved]);

        return redirect()->back();
    }

    public function saved(Request $request)
    {
        $suggestions = $request->user()
            ->outfitSuggestions()
            ->where('is_saved', true)
            ->latest()
            ->get();

        return Inertia::render('Outfits/Saved', [
            'suggestions' => OutfitSuggestionResource::collection($suggestions),
        ]);
    }
}
