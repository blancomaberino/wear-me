<?php

namespace App\Services\Scraper;

use App\Contracts\ScrapedProduct;
use App\Contracts\ScraperProviderContract;
use App\Services\Scraper\Providers\AmazonScraperProvider;
use App\Services\Scraper\Providers\GenericScraperProvider;

class ScraperService
{
    /** @var ScraperProviderContract[] */
    private array $providers;

    public function __construct()
    {
        $this->providers = [
            new AmazonScraperProvider(),
            new GenericScraperProvider(), // fallback, must be last
        ];
    }

    public function scrape(string $url): ScrapedProduct
    {
        foreach ($this->providers as $provider) {
            if ($provider->canHandle($url)) {
                return $provider->scrape($url);
            }
        }

        // Should never reach here since GenericScraperProvider handles all
        throw new \RuntimeException('No scraper provider could handle the provided URL.');
    }
}
