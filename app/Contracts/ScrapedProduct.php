<?php

namespace App\Contracts;

class ScrapedProduct
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $brand,
        public readonly ?float $price,
        public readonly ?string $currency,
        public readonly array $imageUrls,
        public readonly ?string $categoryHint,
        public readonly array $sizeOptions,
        public readonly ?string $material,
        public readonly ?string $description,
        public readonly string $sourceUrl,
        public readonly string $sourceProvider,
    ) {}

    public function primaryImageUrl(): ?string
    {
        return $this->imageUrls[0] ?? null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'brand' => $this->brand,
            'price' => $this->price,
            'currency' => $this->currency,
            'image_urls' => $this->imageUrls,
            'image_url' => $this->primaryImageUrl(),
            'category_hint' => $this->categoryHint,
            'size_options' => $this->sizeOptions,
            'material' => $this->material,
            'description' => $this->description,
            'source_url' => $this->sourceUrl,
            'source_provider' => $this->sourceProvider,
        ];
    }
}
