<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\GarmentController;
use App\Http\Controllers\ModelImageController;
use App\Http\Controllers\OutfitSuggestionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TryOnController;
use App\Http\Controllers\TryOnVideoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Locale
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/palette', [ProfileController::class, 'updatePalette'])->name('profile.palette.update');
    Route::patch('/profile/measurements', [ProfileController::class, 'updateMeasurements'])->name('profile.measurements.update');

    // Model Images
    Route::get('/model-images', [ModelImageController::class, 'index'])->name('model-images.index');
    Route::patch('/model-images/{modelImage}/primary', [ModelImageController::class, 'setPrimary'])->name('model-images.primary');
    Route::delete('/model-images/{modelImage}', [ModelImageController::class, 'destroy'])->name('model-images.destroy');

    // Wardrobe / Garments (reads)
    Route::get('/wardrobe', [GarmentController::class, 'index'])->name('wardrobe.index');
    Route::patch('/wardrobe/{garment}', [GarmentController::class, 'update'])->name('wardrobe.update');
    Route::delete('/wardrobe/{garment}', [GarmentController::class, 'destroy'])->name('wardrobe.destroy');

    // Try-On (reads)
    Route::get('/tryon', [TryOnController::class, 'index'])->name('tryon.index');
    Route::get('/tryon/history', [TryOnController::class, 'history'])->name('tryon.history');
    Route::get('/tryon/{tryOnResult}', [TryOnController::class, 'show'])->name('tryon.show');
    Route::get('/tryon/{tryOnResult}/status', [TryOnController::class, 'status'])->name('tryon.status');
    Route::patch('/tryon/{tryOnResult}/favorite', [TryOnController::class, 'toggleFavorite'])->name('tryon.favorite');

    // Videos (reads)
    Route::get('/videos', [TryOnVideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/history', [TryOnVideoController::class, 'history'])->name('videos.history');
    Route::get('/videos/{video}', [TryOnVideoController::class, 'show'])->name('videos.show');
    Route::get('/videos/{video}/status', [TryOnVideoController::class, 'status'])->name('videos.status');

    // Outfit Suggestions (reads)
    Route::get('/outfits', [OutfitSuggestionController::class, 'index'])->name('outfits.index');
    Route::get('/outfits/saved', [OutfitSuggestionController::class, 'saved'])->name('outfits.saved');
    Route::patch('/outfits/{suggestion}/save', [OutfitSuggestionController::class, 'toggleSaved'])->name('outfits.save');

    // Rate-limited: expensive API-consuming routes (10 per minute)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/tryon', [TryOnController::class, 'store'])->name('tryon.store');
        Route::post('/videos', [TryOnVideoController::class, 'store'])->name('videos.store');
        Route::post('/outfits/generate', [OutfitSuggestionController::class, 'generate'])->name('outfits.generate');
        Route::post('/profile/palette/detect', [ProfileController::class, 'detectPalette'])->name('profile.palette.detect');
    });

    // Rate-limited: upload routes (30 per minute)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/model-images', [ModelImageController::class, 'store'])->name('model-images.store');
        Route::post('/wardrobe', [GarmentController::class, 'store'])->name('wardrobe.store');
    });
});

require __DIR__.'/auth.php';
