<?php

namespace App\Services;

use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class WardrobeService
{
    public function __construct(
        private ImageProcessingService $imageService
    ) {}

    public function storeGarment(User $user, array $data, UploadedFile $image): Garment
    {
        $imageData = $this->imageService->processAndStore($image, 'garments');

        return $user->garments()->create(array_merge($imageData, $data));
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
