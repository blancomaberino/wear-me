<?php

namespace App\Providers;

use App\Contracts\TryOnProviderContract;
use App\Services\TryOn\GeminiTryOnProvider;
use App\Services\TryOn\KlingTryOnProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TryOnProviderContract::class, fn ($app) =>
            match (config('services.tryon.provider')) {
                'gemini' => $app->make(GeminiTryOnProvider::class),
                default  => $app->make(KlingTryOnProvider::class),
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
