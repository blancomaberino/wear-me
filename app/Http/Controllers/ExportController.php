<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateExport;
use App\Models\Export;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function __construct(
        private ExportService $exportService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'include_images' => 'boolean',
            'include_results' => 'boolean',
        ]);

        // Prevent multiple concurrent exports
        $pendingExport = $request->user()->exports()
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($pendingExport) {
            return response()->json([
                'export' => $this->formatExport($pendingExport),
            ]);
        }

        $export = $this->exportService->createExport(
            $request->user(),
            $request->boolean('include_images', true),
            $request->boolean('include_results', false)
        );

        GenerateExport::dispatch($export);

        return response()->json([
            'export' => $this->formatExport($export),
        ]);
    }

    public function status(Request $request, Export $export)
    {
        $this->authorize('view', $export);

        return response()->json([
            'export' => $this->formatExport($export),
        ]);
    }

    public function download(Request $request, Export $export)
    {
        $this->authorize('download', $export);

        if ($export->status !== 'completed' || !$export->file_path || $export->isExpired()) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($export->file_path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->download($fullPath, basename($export->file_path));
    }

    private function formatExport(Export $export): array
    {
        return [
            'id' => $export->id,
            'status' => $export->status,
            'file_size_bytes' => $export->file_size_bytes,
            'download_url' => $export->status === 'completed' ? route('export.download', $export) : null,
            'created_at' => $export->created_at->diffForHumans(),
        ];
    }
}
