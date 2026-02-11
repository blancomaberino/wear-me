<?php

namespace App\Http\Controllers;

use App\Http\Resources\TryOnResultSummaryResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $counts = $this->dashboardService->getCounts($user);

        return Inertia::render('Dashboard', [
            'recentTryOns' => TryOnResultSummaryResource::collection(
                $this->dashboardService->getRecentTryOns($user)
            ),
            'wardrobeStats' => $this->dashboardService->getWardrobeStats($user),
            'modelImageCount' => $counts['modelImageCount'],
            'videoCount' => $counts['videoCount'],
            'savedSuggestionCount' => $counts['savedSuggestionCount'],
            'hasMeasurements' => $user->hasMeasurements(),
        ]);
    }
}
