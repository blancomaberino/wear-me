<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GarmentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LookbookController;
use App\Http\Controllers\ModelImageController;
use App\Http\Controllers\OutfitController;
use App\Http\Controllers\OutfitSuggestionController;
use App\Http\Controllers\OutfitTemplateController;
use App\Http\Controllers\PackingListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicShareController;
use App\Http\Controllers\ShareController;
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

    // Lookbooks
    Route::get('/lookbooks', [LookbookController::class, 'index'])->name('lookbooks.index');
    Route::post('/lookbooks', [LookbookController::class, 'store'])->name('lookbooks.store');
    Route::get('/lookbooks/{lookbook}', [LookbookController::class, 'show'])->name('lookbooks.show');
    Route::patch('/lookbooks/{lookbook}', [LookbookController::class, 'update'])->name('lookbooks.update');
    Route::delete('/lookbooks/{lookbook}', [LookbookController::class, 'destroy'])->name('lookbooks.destroy');
    Route::post('/lookbooks/{lookbook}/items', [LookbookController::class, 'addItem'])->name('lookbooks.items.add');
    Route::delete('/lookbooks/{lookbook}/items/{item}', [LookbookController::class, 'removeItem'])->name('lookbooks.items.remove');
    Route::patch('/lookbooks/{lookbook}/reorder', [LookbookController::class, 'reorder'])->name('lookbooks.reorder');

    // Share Links
    Route::post('/share', [ShareController::class, 'store'])->name('share.store');
    Route::delete('/share/{shareLink}', [ShareController::class, 'destroy'])->name('share.destroy');
    Route::get('/share/my-links', [ShareController::class, 'index'])->name('share.index');

    // Outfit Templates & My Outfits
    Route::get('/outfits/templates', [OutfitTemplateController::class, 'index'])->name('outfits.templates');
    Route::get('/my-outfits', [OutfitController::class, 'index'])->name('my-outfits.index');
    Route::post('/my-outfits', [OutfitController::class, 'store'])->name('my-outfits.store');
    Route::get('/my-outfits/{outfit}', [OutfitController::class, 'show'])->name('my-outfits.show');
    Route::delete('/my-outfits/{outfit}', [OutfitController::class, 'destroy'])->name('my-outfits.destroy');

    // Packing Lists
    Route::get('/packing-lists', [PackingListController::class, 'index'])->name('packing-lists.index');
    Route::post('/packing-lists', [PackingListController::class, 'store'])->name('packing-lists.store');
    Route::get('/packing-lists/{packingList}', [PackingListController::class, 'show'])->name('packing-lists.show');
    Route::patch('/packing-lists/{packingList}', [PackingListController::class, 'update'])->name('packing-lists.update');
    Route::delete('/packing-lists/{packingList}', [PackingListController::class, 'destroy'])->name('packing-lists.destroy');
    Route::post('/packing-lists/{packingList}/items', [PackingListController::class, 'addItem'])->name('packing-lists.items.add');
    Route::delete('/packing-lists/{packingList}/items/{item}', [PackingListController::class, 'removeItem'])->name('packing-lists.items.remove');
    Route::patch('/packing-lists/{packingList}/items/{item}/toggle', [PackingListController::class, 'togglePacked'])->name('packing-lists.items.toggle');

    // Export
    Route::post('/export', [ExportController::class, 'store'])->name('export.store');
    Route::get('/export/{export}/status', [ExportController::class, 'status'])->name('export.status');
    Route::get('/export/{export}/download', [ExportController::class, 'download'])->name('export.download');

    // Rate-limited: expensive API-consuming routes (10 per minute)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/tryon', [TryOnController::class, 'store'])->name('tryon.store');
        Route::post('/videos', [TryOnVideoController::class, 'store'])->name('videos.store');
        Route::post('/outfits/generate', [OutfitSuggestionController::class, 'generate'])->name('outfits.generate');
        Route::post('/profile/palette/detect', [ProfileController::class, 'detectPalette'])->name('profile.palette.detect');
        Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
        Route::post('/import/confirm', [ImportController::class, 'confirm'])->name('import.confirm');
    });

    // Rate-limited: upload routes (30 per minute)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/model-images', [ModelImageController::class, 'store'])->name('model-images.store');
        Route::post('/wardrobe', [GarmentController::class, 'store'])->name('wardrobe.store');
        Route::post('/wardrobe/bulk', [GarmentController::class, 'bulkStore'])->name('wardrobe.bulk');
    });
});

// Public share routes (no auth)
Route::get('/s/{token}', [PublicShareController::class, 'show'])->name('share.public');
Route::post('/s/{token}/react', [PublicShareController::class, 'react'])->name('share.react')->middleware('throttle:10,1');

require __DIR__.'/auth.php';
