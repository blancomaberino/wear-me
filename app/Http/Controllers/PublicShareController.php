<?php

namespace App\Http\Controllers;

use App\Services\ShareService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicShareController extends Controller
{
    public function __construct(
        private ShareService $shareService
    ) {}

    public function show(string $token)
    {
        $link = $this->shareService->resolveShareLink($token);

        if (!$link) {
            abort(404);
        }

        $link->load('shareable');

        $content = null;
        $type = class_basename($link->shareable_type);

        if ($link->shareable) {
            if ($type === 'TryOnResult') {
                $link->shareable->load('garments');
            } elseif ($type === 'Lookbook') {
                $link->shareable->load(['items' => function ($q) {
                    $q->orderBy('sort_order')->with('itemable');
                }]);
            }
            $content = $link->shareable;
        }

        return Inertia::render('Public/SharedView', [
            'shareLink' => [
                'token' => $link->token,
                'shareable_type' => $type,
                'view_count' => $link->view_count,
                'reactions_summary' => $link->reactions_summary,
            ],
            'content' => $content,
        ]);
    }

    public function react(Request $request, string $token)
    {
        $link = $this->shareService->resolveShareLink($token);

        if (!$link) {
            abort(404);
        }

        $request->validate([
            'type' => 'required|string|in:thumbs_up,thumbs_down,heart,fire',
        ]);

        $visitorHash = hash('sha256', $request->ip() . $request->userAgent());

        $added = $this->shareService->addReaction(
            $link,
            $request->input('type'),
            $visitorHash
        );

        return response()->json([
            'added' => $added,
            'reactions_summary' => $link->fresh()->reactions_summary,
        ]);
    }
}
