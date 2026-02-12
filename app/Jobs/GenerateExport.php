<?php

namespace App\Jobs;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GenerateExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MAX_EXPORT_SIZE_BYTES = 500 * 1024 * 1024; // 500MB

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        private Export $export
    ) {}

    public function handle(): void
    {
        try {
            $this->export->update(['status' => 'processing']);

            $user = $this->export->user;
            $timestamp = now()->format('Y-m-d');
            $zipName = "wardrobe-export-{$timestamp}-{$this->export->id}.zip";
            $zipPath = "exports/{$zipName}";
            $fullZipPath = Storage::disk('local')->path($zipPath);

            // Ensure directory exists
            Storage::disk('local')->makeDirectory('exports');

            $zip = new ZipArchive();
            if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Could not create ZIP archive');
            }

            // Export garment data
            $garments = $user->garments()->get();
            $garmentData = $garments->map(function ($g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'category' => $g->category->value,
                    'description' => $g->description,
                    'brand' => $g->brand,
                    'material' => $g->material,
                    'size_label' => $g->size_label,
                    'color_tags' => $g->color_tags,
                    'measurements' => [
                        'chest_cm' => $g->measurement_chest_cm,
                        'length_cm' => $g->measurement_length_cm,
                        'waist_cm' => $g->measurement_waist_cm,
                        'inseam_cm' => $g->measurement_inseam_cm,
                        'shoulder_cm' => $g->measurement_shoulder_cm,
                        'sleeve_cm' => $g->measurement_sleeve_cm,
                    ],
                    'source_url' => $g->source_url,
                    'created_at' => $g->created_at->toISOString(),
                ];
            })->all();

            // Export user measurements
            $measurements = $user->getFormattedMeasurements();

            // Export color palette
            $palette = $user->color_palette;

            // Build data.json
            $data = [
                'exported_at' => now()->toISOString(),
                'user' => [
                    'name' => $user->name,
                    'measurements' => $measurements,
                    'color_palette' => $palette,
                ],
                'garments' => $garmentData,
                'garment_count' => count($garmentData),
            ];

            $zip->addFromString('data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Add README
            $readme = "Virtual Wardrobe Export\n";
            $readme .= "======================\n\n";
            $readme .= "Exported: {$timestamp}\n";
            $readme .= "Garments: " . count($garmentData) . "\n\n";
            $readme .= "Files:\n";
            $readme .= "- data.json: All metadata (garments, measurements, palette)\n";
            if ($this->export->include_images) {
                $readme .= "- garments/: Garment images\n";
            }
            if ($this->export->include_results) {
                $readme .= "- results/: Try-on result images\n";
            }
            $zip->addFromString('README.txt', $readme);

            // Add garment images
            if ($this->export->include_images) {
                foreach ($garments as $garment) {
                    $path = Storage::disk('public')->path($garment->path);
                    if (file_exists($path)) {
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $safeName = $garment->id . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $garment->name ?? 'garment');
                        $zip->addFile($path, "garments/{$safeName}.{$ext}");
                    }
                }
            }

            // Add try-on results
            if ($this->export->include_results) {
                $results = $user->tryonResults()
                    ->where('status', 'completed')
                    ->whereNotNull('result_path')
                    ->get();

                foreach ($results as $result) {
                    $path = Storage::disk('public')->path($result->result_path);
                    if (file_exists($path)) {
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $zip->addFile($path, "results/tryon_{$result->id}.{$ext}");
                    }
                }
            }

            if ($zip->close() !== true) {
                throw new \RuntimeException('Failed to finalize ZIP archive');
            }

            // Enforce maximum export size
            $fileSize = filesize($fullZipPath);
            if ($fileSize > self::MAX_EXPORT_SIZE_BYTES) {
                unlink($fullZipPath);
                throw new \RuntimeException('Export exceeds maximum allowed size.');
            }

            $this->export->update([
                'status' => 'completed',
                'file_path' => $zipPath,
                'file_size_bytes' => $fileSize,
                'expires_at' => now()->addHours(24),
            ]);

        } catch (\Throwable $e) {
            Log::error('GenerateExport failed', [
                'export_id' => $this->export->id,
                'error' => $e->getMessage(),
            ]);

            // Only mark as permanently failed on last attempt
            if ($this->attempts() >= $this->tries) {
                $this->export->update(['status' => 'failed']);
            } else {
                $this->export->update(['status' => 'pending']);
            }

            throw $e;
        }
    }
}
