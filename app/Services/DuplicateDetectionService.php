<?php

namespace App\Services;

use App\Models\Garment;
use App\Models\User;
use Illuminate\Support\Collection;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class DuplicateDetectionService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Compute perceptual hash for an image.
     * Uses average hash: resize to 8x8, grayscale, compare to mean.
     */
    public function computeHash(string $imagePath): string
    {
        $fullPath = Storage::disk('public')->path($imagePath);
        $image = $this->manager->read($fullPath);

        // Resize to 8x8 and convert to grayscale
        $image->resize(8, 8);
        $image->greyscale();

        // Get pixel values
        $pixels = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $color = $image->pickColor($x, $y);
                $pixels[] = $color->red(); // grayscale so R=G=B
            }
        }

        // Compute mean
        $mean = array_sum($pixels) / count($pixels);

        // Generate hash: 1 if above mean, 0 if below
        $hash = '';
        foreach ($pixels as $pixel) {
            $hash .= $pixel >= $mean ? '1' : '0';
        }

        return $hash;
    }

    /**
     * Find duplicate garments for a user based on Hamming distance.
     */
    public function findDuplicates(User $user, string $hash, int $threshold = 10): Collection
    {
        $garments = $user->garments()
            ->whereNotNull('perceptual_hash')
            ->get(['id', 'name', 'thumbnail_path', 'path', 'perceptual_hash']);

        return $garments->filter(function (Garment $garment) use ($hash, $threshold) {
            $distance = $this->hammingDistance($hash, $garment->perceptual_hash);
            return $distance <= $threshold;
        })->map(function (Garment $garment) use ($hash) {
            $distance = $this->hammingDistance($hash, $garment->perceptual_hash);
            $similarity = round((1 - $distance / 64) * 100);
            return [
                'id' => $garment->id,
                'name' => $garment->name,
                'thumbnail_url' => $garment->thumbnail_url,
                'similarity' => $similarity,
            ];
        })->values();
    }

    /**
     * Compute Hamming distance between two binary hash strings.
     */
    public function hammingDistance(string $hash1, string $hash2): int
    {
        if (strlen($hash1) !== strlen($hash2)) {
            return 64; // Max distance
        }

        $distance = 0;
        for ($i = 0; $i < strlen($hash1); $i++) {
            if ($hash1[$i] !== $hash2[$i]) {
                $distance++;
            }
        }

        return $distance;
    }
}
