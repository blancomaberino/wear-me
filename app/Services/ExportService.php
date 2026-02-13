<?php

namespace App\Services;

use App\Models\Export;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    public function createExport(User $user, bool $includeImages = true, bool $includeResults = false): Export
    {
        return $user->exports()->create([
            'status' => 'pending',
            'include_images' => $includeImages,
            'include_results' => $includeResults,
        ]);
    }

    public function cleanExpiredExports(): int
    {
        $expired = Export::where('status', 'completed')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $export) {
            if ($export->file_path) {
                Storage::disk('local')->delete($export->file_path);
            }
            $export->update(['status' => 'expired', 'file_path' => null]);
            $count++;
        }

        return $count;
    }
}
