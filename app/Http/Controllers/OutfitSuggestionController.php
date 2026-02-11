<?php

namespace App\Http\Controllers;

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
            ->paginate(10)
            ->through(fn (OutfitSuggestion $s) => [
                'id' => $s->id,
                'garment_ids' => $s->garment_ids,
                'garments' => $s->garments()->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name ?? $g->original_filename,
                    'thumbnail_url' => $g->thumbnail_url,
                    'category' => $g->category->value,
                ]),
                'suggestion_text' => $s->suggestion_text,
                'occasion' => $s->occasion,
                'is_saved' => $s->is_saved,
                'created_at' => $s->created_at->diffForHumans(),
            ]);

        $garmentCount = $request->user()->garments()->count();

        return Inertia::render('Outfits/Suggestions', [
            'suggestions' => $suggestions,
            'garmentCount' => $garmentCount,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'occasion' => 'required|string|in:casual,work,evening,sport,date',
        ]);

        GenerateOutfitSuggestion::dispatch($request->user(), $request->occasion);

        return redirect()->back()->with('success', __('messages.outfit_generating'));
    }

    public function toggleSaved(Request $request, OutfitSuggestion $suggestion)
    {
        if ($suggestion->user_id !== $request->user()->id) {
            abort(403);
        }

        $suggestion->update(['is_saved' => !$suggestion->is_saved]);

        return redirect()->back();
    }

    public function saved(Request $request)
    {
        $suggestions = $request->user()
            ->outfitSuggestions()
            ->where('is_saved', true)
            ->latest()
            ->get()
            ->map(fn (OutfitSuggestion $s) => [
                'id' => $s->id,
                'garment_ids' => $s->garment_ids,
                'garments' => $s->garments()->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name ?? $g->original_filename,
                    'thumbnail_url' => $g->thumbnail_url,
                    'category' => $g->category->value,
                ]),
                'suggestion_text' => $s->suggestion_text,
                'occasion' => $s->occasion,
                'is_saved' => $s->is_saved,
                'created_at' => $s->created_at->diffForHumans(),
            ]);

        return Inertia::render('Outfits/Saved', [
            'suggestions' => $suggestions,
        ]);
    }
}
