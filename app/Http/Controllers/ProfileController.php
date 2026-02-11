<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ColorPaletteService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'hasMeasurements' => $request->user()->hasMeasurements(),
            'colorPalette' => $request->user()->color_palette ?? [],
            'modelImages' => $request->user()->modelImages()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'thumbnail_url' => $image->thumbnail_url ?? $image->url,
                ]),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function updatePalette(Request $request): RedirectResponse
    {
        $request->validate([
            'colors' => 'required|array|max:50',
            'colors.*' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $request->user()->update(['color_palette' => $request->colors]);

        return Redirect::route('profile.edit');
    }

    public function updateMeasurements(Request $request): RedirectResponse
    {
        $request->validate([
            'measurement_unit' => 'required|in:metric,imperial',
            'height_cm' => 'nullable|numeric|min:50|max:300',
            'weight_kg' => 'nullable|numeric|min:20|max:500',
            'chest_cm' => 'nullable|numeric|min:30|max:200',
            'waist_cm' => 'nullable|numeric|min:30|max:200',
            'hips_cm' => 'nullable|numeric|min:30|max:200',
            'inseam_cm' => 'nullable|numeric|min:20|max:150',
            'shoe_size_eu' => 'nullable|numeric|min:20|max:60',
        ]);

        $request->user()->update($request->only([
            'measurement_unit', 'height_cm', 'weight_kg', 'chest_cm',
            'waist_cm', 'hips_cm', 'inseam_cm', 'shoe_size_eu',
        ]));

        return Redirect::route('profile.edit')->with('success', __('messages.measurements_updated'));
    }

    public function detectPalette(Request $request, ColorPaletteService $colorPaletteService): JsonResponse
    {
        $request->validate([
            'model_image_id' => 'required|integer',
            'color_count' => 'sometimes|integer|min:1|max:50',
        ]);

        $modelImage = $request->user()->modelImages()->findOrFail($request->model_image_id);

        $colorCount = $request->integer('color_count', 8);
        $colors = $colorPaletteService->detectFromImage($modelImage->path, $colorCount);

        return response()->json(['colors' => $colors]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
