<?php

namespace App\Services\Scraper\Providers;

use App\Contracts\ScrapedProduct;
use App\Contracts\ScraperProviderContract;
use App\Services\Scraper\UrlValidator;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class GenericScraperProvider implements ScraperProviderContract
{
    public function canHandle(string $url): bool
    {
        return true; // Fallback — always handles
    }

    public function scrape(string $url): ScrapedProduct
    {
        $resolvedIp = UrlValidator::validateExternalUrl($url);

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept' => 'text/html,application/xhtml+xml',
        ])->withOptions(UrlValidator::getSecureRequestOptions($url, $resolvedIp))
          ->timeout(15)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("Failed to fetch URL: HTTP {$response->status()}");
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $name = $this->getMeta($crawler, 'og:title')
             ?: $this->getMeta($crawler, 'twitter:title')
             ?: $this->getTag($crawler, 'title')
             ?: 'Unknown Product';

        $imageUrl = $this->getMeta($crawler, 'og:image')
                 ?: $this->getMeta($crawler, 'twitter:image');

        $description = $this->getMeta($crawler, 'og:description')
                    ?: $this->getMeta($crawler, 'description');

        $brand = $this->getMeta($crawler, 'og:site_name');

        $host = parse_url($url, PHP_URL_HOST);
        $provider = preg_replace('/^www\./', '', $host ?? 'unknown');

        return new ScrapedProduct(
            name: trim($name),
            brand: $brand ? trim($brand) : null,
            price: null,
            currency: null,
            imageUrls: $imageUrl ? [$imageUrl] : [],
            categoryHint: null,
            sizeOptions: [],
            material: null,
            description: $description ? mb_substr(trim($description), 0, 500) : null,
            sourceUrl: $url,
            sourceProvider: $provider,
        );
    }

    public function name(): string
    {
        return 'generic';
    }

    private function getMeta(Crawler $crawler, string $property): ?string
    {
        try {
            $node = $crawler->filter("meta[property=\"{$property}\"], meta[name=\"{$property}\"]")->first();
            return $node->count() > 0 ? $node->attr('content') : null;
        } catch (\Exception) {
            return null;
        }
    }

    private function getTag(Crawler $crawler, string $tag): ?string
    {
        try {
            $node = $crawler->filter($tag)->first();
            return $node->count() > 0 ? trim($node->text()) : null;
        } catch (\Exception) {
            return null;
        }
    }
}
