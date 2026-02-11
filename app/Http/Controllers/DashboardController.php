<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $recentTryOns = $user->tryonResults()
            ->with(['modelImage', 'garment', 'garments'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($result) {
                $garmentsList = $result->garments;
                if ($garmentsList->isEmpty() && $result->garment) {
                    $garmentsList = collect([$result->garment]);
                }

                $combinedName = $garmentsList
                    ->map(fn ($g) => $g->name ?? $g->original_filename)
                    ->join(' + ');

                return [
                    'id' => $result->id,
                    'status' => $result->status->value,
                    'result_url' => $result->result_url,
                    'is_favorite' => $result->is_favorite,
                    'created_at' => $result->created_at->diffForHumans(),
                    'model_image' => [
                        'thumbnail_url' => $result->modelImage?->thumbnail_url,
                    ],
                    'garment' => [
                        'name' => $combinedName,
                        'thumbnail_url' => $garmentsList->first()?->thumbnail_url,
                        'category' => $garmentsList->first()?->category?->value,
                    ],
                ];
            });

        $wardrobeStats = [
            'total' => $user->garments()->count(),
            'upper' => $user->garments()->where('category', 'upper')->count(),
            'lower' => $user->garments()->where('category', 'lower')->count(),
            'dress' => $user->garments()->where('category', 'dress')->count(),
        ];

        $modelImageCount = $user->modelImages()->count();
        $videoCount = $user->tryonVideos()->count();
        $suggestionCount = $user->outfitSuggestions()->where('is_saved', true)->count();

        return Inertia::render('Dashboard', [
            'recentTryOns' => $recentTryOns,
            'wardrobeStats' => $wardrobeStats,
            'modelImageCount' => $modelImageCount,
            'savedSuggestionCount' => $suggestionCount,
            'hasMeasurements' => $user->hasMeasurements(),
        ]);
    }
}
