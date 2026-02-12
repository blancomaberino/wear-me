<?php

namespace App\Services\Scraper\Providers;

use App\Contracts\ScrapedProduct;
use App\Contracts\ScraperProviderContract;
use App\Services\Scraper\UrlValidator;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class AmazonScraperProvider implements ScraperProviderContract
{
    public function canHandle(string $url): bool
    {
        return (bool) preg_match('/amazon\.(com|co\.uk|es|de|fr|it|ca|com\.au|co\.jp)/i', $url);
    }

    public function scrape(string $url): ScrapedProduct
    {
        $resolvedIp = UrlValidator::validateExternalUrl($url);

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
        ])->withOptions(UrlValidator::getSecureRequestOptions($url, $resolvedIp))
          ->timeout(15)->get($url);

        $response->throw();

        $html = $response->body();
        $crawler = new Crawler($html);

        $name = $this->extractText($crawler, '#productTitle')
             ?: $this->extractText($crawler, 'h1.product-title-word-break')
             ?: 'Unknown Product';

        $brand = $this->extractText($crawler, '#bylineInfo')
              ?: $this->extractText($crawler, '.po-brand .po-break-word');
        if ($brand) {
            $brand = preg_replace('/^(Visit the |Brand: )/i', '', $brand);
        }

        $imageUrls = [];
        $crawler->filter('#imgTagWrapperId img, #landingImage, #main-image')->each(function (Crawler $node) use (&$imageUrls) {
            $src = $node->attr('data-old-hires') ?: $node->attr('src');
            if ($src && !str_contains($src, 'sprite') && !str_contains($src, 'grey-pixel')) {
                $imageUrls[] = $src;
            }
        });

        $description = $this->extractText($crawler, '#feature-bullets .a-list-item')
                    ?: $this->extractText($crawler, '#productDescription p');

        $material = null;
        $crawler->filter('#productDetails_techSpec_section_1 tr, .product-facts-detail')->each(function (Crawler $node) use (&$material) {
            $text = $node->text();
            if (stripos($text, 'Material') !== false || stripos($text, 'Fabric') !== false) {
                $material = trim(preg_replace('/^.*?(Material|Fabric)[:\s]*/i', '', $text));
            }
        });

        return new ScrapedProduct(
            name: trim($name),
            brand: $brand ? trim($brand) : null,
            price: null,
            currency: null,
            imageUrls: array_unique($imageUrls),
            categoryHint: null,
            sizeOptions: [],
            material: $material,
            description: $description ? mb_substr(trim($description), 0, 500) : null,
            sourceUrl: $url,
            sourceProvider: 'amazon',
        );
    }

    public function name(): string
    {
        return 'amazon';
    }

    private function extractText(Crawler $crawler, string $selector): ?string
    {
        try {
            $node = $crawler->filter($selector)->first();
            return $node->count() > 0 ? trim($node->text()) : null;
        } catch (\Exception) {
            return null;
        }
    }
}
