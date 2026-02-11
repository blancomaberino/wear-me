<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageProcessingService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function processAndStore(UploadedFile $file, string $directory): array
    {
        $image = $this->manager->read($file->getPathname());
        $width = $image->width();
        $height = $image->height();

        $path = $file->store($directory, 'public');

        $thumbnailPath = $this->generateThumbnail($file, $directory);

        return [
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'width' => $width,
            'height' => $height,
            'size_bytes' => $file->getSize(),
            'original_filename' => $file->getClientOriginalName(),
        ];
    }

    private function generateThumbnail(UploadedFile $file, string $directory): string
    {
        $image = $this->manager->read($file->getPathname());
        $image->cover(300, 300);

        $thumbnailName = 'thumb_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $thumbnailPath = $directory . '/thumbnails/' . $thumbnailName;

        Storage::disk('public')->put($thumbnailPath, $image->toJpeg(80));

        return $thumbnailPath;
    }
}
