<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScrapeUrlRequest;
use App\Models\User;
use App\Services\Scraper\ScraperService;
use App\Services\Scraper\UrlValidator;
use App\Services\WardrobeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImportController extends Controller
{
    public function __construct(
        private ScraperService $scraperService,
        private WardrobeService $wardrobeService,
    ) {}

    public function preview(ScrapeUrlRequest $request)
    {
        try {
            $product = $this->scraperService->scrape($request->input('url'));

            return response()->json([
                'success' => true,
                'product' => $product->toArray(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Import preview failed', [
                'url' => $request->input('url'),
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            return response()->json([
                'success' => false,
                'error' => __('import.importError'),
            ], 422);
        }
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'source_url' => 'required|url|max:2048',
            'image_url' => 'required|url',
            'name' => 'nullable|string|max:255',
            'category' => 'required|in:upper,lower,dress',
            'brand' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'source_provider' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        if ($user->garments()->count() >= User::MAX_GARMENTS) {
            return redirect()->back()->withErrors(['url' => __('messages.garmentLimitReached')]);
        }

        $tempPath = null;

        try {
            // Validate URL is external (SSRF protection) and pin resolved IP
            $resolvedIp = UrlValidator::validateExternalUrl($request->input('image_url'));

            // Download the image with size limit (10MB), pinned DNS
            $imageResponse = Http::withOptions(
                UrlValidator::getSecureRequestOptions($request->input('image_url'), $resolvedIp)
            )->timeout(15)->get($request->input('image_url'));

            if (!$imageResponse->successful()) {
                return redirect()->back()->withErrors(['url' => __('import.importError')]);
            }

            $body = $imageResponse->body();
            if (strlen($body) > 10 * 1024 * 1024) {
                return redirect()->back()->withErrors(['url' => __('import.importError')]);
            }

            // Validate response is actually an image
            $contentType = $imageResponse->header('Content-Type');
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!$contentType || !in_array(explode(';', $contentType)[0], $allowedTypes, true)) {
                return redirect()->back()->withErrors(['url' => __('import.importError')]);
            }

            // Store as temp file
            $extension = $this->guessExtension(explode(';', $contentType)[0]);
            $tempName = 'import_' . uniqid() . '.' . $extension;
            $tempPath = 'temp/' . $tempName;
            Storage::disk('local')->put($tempPath, $body);

            $fullPath = Storage::disk('local')->path($tempPath);
            $file = new UploadedFile($fullPath, $tempName, explode(';', $contentType)[0], null, true);

            $this->wardrobeService->storeGarment(
                $user,
                [
                    'category' => $request->input('category'),
                    'name' => $request->input('name'),
                    'brand' => $request->input('brand'),
                    'material' => $request->input('material'),
                    'source_url' => $request->input('source_url'),
                    'source_provider' => $request->input('source_provider'),
                ],
                $file
            );

            return redirect()->back()->with('success', __('import.importSuccess'));
        } catch (\Throwable $e) {
            Log::error('Import confirm failed', [
                'image_url' => $request->input('image_url'),
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            return redirect()->back()->withErrors(['url' => __('import.importError')]);
        } finally {
            if ($tempPath && Storage::disk('local')->exists($tempPath)) {
                Storage::disk('local')->delete($tempPath);
            }
        }
    }

    private function guessExtension(?string $contentType): string
    {
        return match ($contentType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
    }
}
