<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GarmentColorExtractor
{
    public function __construct(
        private ColorNameMapper $colorNameMapper
    ) {}

    /**
     * Extract dominant colors from a garment image.
     *
     * @param string $imagePath Storage path relative to 'public' disk
     * @return array Array of colors with 'hex' and 'name' keys
     */
    public function extract(string $imagePath): array
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);

            if (!file_exists($fullPath)) {
                Log::warning('GarmentColorExtractor: Image file not found', ['path' => $imagePath]);
                return [];
            }

            $image = $this->loadImage($fullPath);
            if (!$image) {
                Log::warning('GarmentColorExtractor: Failed to load image', ['path' => $imagePath]);
                return [];
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Resize to 100x100 for speed
            $resized = imagecreatetruecolor(100, 100);
            // Preserve alpha for PNG/WebP transparency
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, 99, 99, $transparent);
            imagealphablending($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, 100, 100, $width, $height);
            imagedestroy($image);

            // Sample all pixels
            $pixels = $this->samplePixels($resized, 100, 100);
            imagedestroy($resized);

            if (empty($pixels)) {
                return [];
            }

            // Filter background
            $filtered = $this->filterBackground($pixels);

            // If too few pixels remain, relax filter
            if (count($filtered) < 100) {
                $filtered = array_filter($pixels, function ($pixel) {
                    $r = $pixel[0];
                    $g = $pixel[1];
                    $b = $pixel[2];
                    // Only skip pure white and pure black
                    return !($r > 250 && $g > 250 && $b > 250) && !($r < 5 && $g < 5 && $b < 5);
                });
            }

            if (count($filtered) < 10) {
                return [];
            }

            // Determine k based on pixel count
            $k = min(5, max(1, intdiv(count($filtered), 200)));

            // Run k-means clustering
            $clusters = $this->kMeansClustering(array_values($filtered), $k);

            // Filter clusters with < 5% of total pixels
            $totalPixels = count($filtered);
            $minClusterSize = max(1, intdiv($totalPixels, 20)); // 5%

            $significantClusters = array_filter($clusters, function ($cluster) use ($minClusterSize) {
                return $cluster['size'] >= $minClusterSize;
            });

            if (empty($significantClusters)) {
                // If all filtered out, take the largest cluster
                $significantClusters = [array_reduce($clusters, function ($carry, $item) {
                    return (!$carry || $item['size'] > $carry['size']) ? $item : $carry;
                })];
            }

            // Convert to hex and name
            $colors = [];
            foreach ($significantClusters as $cluster) {
                $hex = $this->rgbToHex(
                    (int)round($cluster['centroid'][0]),
                    (int)round($cluster['centroid'][1]),
                    (int)round($cluster['centroid'][2])
                );
                $name = $this->colorNameMapper->toName($hex);
                $colors[] = ['hex' => $hex, 'name' => $name];
            }

            return array_slice($colors, 0, 5);

        } catch (\Exception $e) {
            Log::error('GarmentColorExtractor: Exception during extraction', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Load image from file system, detecting format automatically.
     */
    private function loadImage(string $fullPath): ?\GdImage
    {
        $imageInfo = @getimagesize($fullPath);
        if (!$imageInfo) {
            return null;
        }

        $mimeType = $imageInfo['mime'];

        return match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($fullPath),
            'image/png' => @imagecreatefrompng($fullPath),
            'image/webp' => @imagecreatefromwebp($fullPath),
            'image/gif' => @imagecreatefromgif($fullPath),
            default => null,
        };
    }

    /**
     * Sample all pixels from the image.
     *
     * @return array Array of [r, g, b] tuples
     */
    private function samplePixels(\GdImage $image, int $width, int $height): array
    {
        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $alpha = ($rgb >> 24) & 0x7F;
                // Skip fully or mostly transparent pixels (alpha > 64 out of 127)
                if ($alpha > 64) {
                    continue;
                }
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $pixels[] = [$r, $g, $b];
            }
        }
        return $pixels;
    }

    /**
     * Filter out background pixels (white, near-white, black, near-black, light gray).
     */
    private function filterBackground(array $pixels): array
    {
        return array_filter($pixels, function ($pixel) {
            $r = $pixel[0];
            $g = $pixel[1];
            $b = $pixel[2];
            $avg = ($r + $g + $b) / 3;

            // Skip white
            if ($r > 240 && $g > 240 && $b > 240) {
                return false;
            }

            // Skip near-white (high average)
            if ($avg > 235) {
                return false;
            }

            // Skip near-black
            if ($avg < 20) {
                return false;
            }

            // Skip very light gray (all channels within 10 of each other and high avg)
            $maxChannel = max($r, $g, $b);
            $minChannel = min($r, $g, $b);
            if ($maxChannel - $minChannel <= 10 && $avg > 200) {
                return false;
            }

            return true;
        });
    }

    /**
     * K-means clustering on RGB pixels.
     *
     * @param array $pixels Array of [r, g, b] tuples
     * @param int $k Number of clusters
     * @param int $maxIterations Maximum iterations
     * @return array Array of clusters with 'centroid' and 'size'
     */
    private function kMeansClustering(array $pixels, int $k, int $maxIterations = 20): array
    {
        if (count($pixels) < $k) {
            $k = count($pixels);
        }

        // Initialize centroids randomly from sampled pixels
        $centroids = [];
        $indices = array_rand($pixels, $k);
        if (!is_array($indices)) {
            $indices = [$indices];
        }
        foreach ($indices as $idx) {
            $centroids[] = $pixels[$idx];
        }

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            // Assign pixels to nearest centroid
            $assignments = array_fill(0, $k, []);

            foreach ($pixels as $pixel) {
                $minDist = PHP_FLOAT_MAX;
                $bestCluster = 0;

                foreach ($centroids as $idx => $centroid) {
                    $dist = $this->euclideanDistance($pixel, $centroid);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $bestCluster = $idx;
                    }
                }

                $assignments[$bestCluster][] = $pixel;
            }

            // Recompute centroids
            $newCentroids = [];
            foreach ($assignments as $cluster) {
                if (empty($cluster)) {
                    // Keep old centroid if cluster is empty
                    $newCentroids[] = $centroids[count($newCentroids)];
                    continue;
                }

                $rSum = $gSum = $bSum = 0;
                foreach ($cluster as $pixel) {
                    $rSum += $pixel[0];
                    $gSum += $pixel[1];
                    $bSum += $pixel[2];
                }
                $count = count($cluster);
                $newCentroids[] = [
                    $rSum / $count,
                    $gSum / $count,
                    $bSum / $count,
                ];
            }

            // Check for convergence
            $converged = true;
            foreach ($centroids as $idx => $centroid) {
                if ($this->euclideanDistance($centroid, $newCentroids[$idx]) > 1.0) {
                    $converged = false;
                    break;
                }
            }

            $centroids = $newCentroids;

            if ($converged) {
                break;
            }
        }

        // Final assignment to get cluster sizes
        $clusters = [];
        foreach ($centroids as $idx => $centroid) {
            $clusters[$idx] = [
                'centroid' => $centroid,
                'size' => 0,
            ];
        }

        foreach ($pixels as $pixel) {
            $minDist = PHP_FLOAT_MAX;
            $bestCluster = 0;

            foreach ($centroids as $idx => $centroid) {
                $dist = $this->euclideanDistance($pixel, $centroid);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $bestCluster = $idx;
                }
            }

            $clusters[$bestCluster]['size']++;
        }

        // Sort by size (largest first)
        usort($clusters, function ($a, $b) {
            return $b['size'] <=> $a['size'];
        });

        return $clusters;
    }

    /**
     * Compute Euclidean distance between two RGB pixels.
     */
    private function euclideanDistance(array $p1, array $p2): float
    {
        $dr = $p1[0] - $p2[0];
        $dg = $p1[1] - $p2[1];
        $db = $p1[2] - $p2[2];
        return sqrt($dr * $dr + $dg * $dg + $db * $db);
    }

    /**
     * Convert RGB to hex string.
     */
    private function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
