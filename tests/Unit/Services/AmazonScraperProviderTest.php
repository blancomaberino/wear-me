<?php

namespace Tests\Unit\Services;

use App\Services\Scraper\Providers\AmazonScraperProvider;
use Tests\TestCase;

class AmazonScraperProviderTest extends TestCase
{
    private AmazonScraperProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new AmazonScraperProvider();
    }

    public function test_can_handle_amazon_com(): void
    {
        $this->assertTrue($this->provider->canHandle('https://www.amazon.com/dp/B08N5WRWNW'));
    }

    public function test_can_handle_amazon_co_uk(): void
    {
        $this->assertTrue($this->provider->canHandle('https://www.amazon.co.uk/dp/B08N5WRWNW'));
    }

    public function test_can_handle_amazon_es(): void
    {
        $this->assertTrue($this->provider->canHandle('https://www.amazon.es/dp/B08N5WRWNW'));
    }

    public function test_can_handle_amazon_de(): void
    {
        $this->assertTrue($this->provider->canHandle('https://www.amazon.de/dp/B08N5WRWNW'));
    }

    public function test_can_handle_amazon_com_au(): void
    {
        $this->assertTrue($this->provider->canHandle('https://www.amazon.com.au/dp/B08N5WRWNW'));
    }

    public function test_can_handle_amazon_co_jp(): void
    {
        $this->assertTrue($this->provider->canHandle('https://www.amazon.co.jp/dp/B08N5WRWNW'));
    }

    public function test_rejects_non_amazon_url(): void
    {
        $this->assertFalse($this->provider->canHandle('https://www.ebay.com/itm/123'));
    }

    public function test_rejects_amazon_substring_in_path(): void
    {
        $this->assertFalse($this->provider->canHandle('https://evil.com/amazon.com/product'));
    }

    public function test_rejects_fake_amazon_domain(): void
    {
        $this->assertFalse($this->provider->canHandle('https://fakamazon.com/dp/123'));
    }

    public function test_rejects_amazon_as_subdomain_of_other_domain(): void
    {
        $this->assertFalse($this->provider->canHandle('https://amazon.com.evil.com/product'));
    }

    public function test_handles_url_without_www(): void
    {
        $this->assertTrue($this->provider->canHandle('https://amazon.com/dp/B08N5WRWNW'));
    }

    public function test_handles_url_with_subdomain(): void
    {
        $this->assertTrue($this->provider->canHandle('https://smile.amazon.com/dp/B08N5WRWNW'));
    }

    public function test_rejects_invalid_url(): void
    {
        $this->assertFalse($this->provider->canHandle('not-a-url'));
    }

    public function test_name_returns_amazon(): void
    {
        $this->assertEquals('amazon', $this->provider->name());
    }
}
