<?php

namespace App\Services;

use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class WardrobeService
{
    public function __construct(
        private ImageProcessingService $imageService,
        private DuplicateDetectionService $duplicateService,
        private GarmentColorExtractor $colorExtractor,
    ) {}

    public function storeGarment(User $user, array $data, UploadedFile $image): Garment
    {
        $imageData = $this->imageService->processAndStore($image, 'garments');

        $garment = $user->garments()->create(array_merge($imageData, $data));

        // Compute and store perceptual hash
        try {
            $hash = $this->duplicateService->computeHash($garment->path);
            $garment->update(['perceptual_hash' => $hash]);
        } catch (\Throwable $e) {
            // Non-critical: don't fail the upload if hash computation fails
        }

        // Auto-detect garment colors
        try {
            $colors = $this->colorExtractor->extract($garment->path);
            if (!empty($colors)) {
                $garment->update(['color_tags' => $colors]);
            }
        } catch (\Throwable $e) {
            // Non-critical: don't fail the upload if color detection fails
        }

        return $garment;
    }

    public function storeModelImage(User $user, UploadedFile $image): ModelImage
    {
        $data = $this->imageService->processAndStore($image, 'model-images');

        return $user->modelImages()->create($data);
    }

    public function deleteGarment(Garment $garment): void
    {
        $garment->delete();
    }

    public function deleteModelImage(ModelImage $modelImage): void
    {
        $modelImage->delete();
    }
}
