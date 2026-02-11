<?php

namespace App\Contracts;

interface ScraperProviderContract
{
    public function canHandle(string $url): bool;
    public function scrape(string $url): ScrapedProduct;
    public function name(): string;
}
