<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateShareLinkRequest;
use App\Http\Resources\ShareLinkResource;
use App\Models\ShareLink;
use App\Services\ShareService;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function __construct(
        private ShareService $shareService
    ) {}

    public function index(Request $request)
    {
        $links = $request->user()
            ->shareLinks()
            ->with('reactions')
            ->latest()
            ->get();

        return response()->json([
            'links' => ShareLinkResource::collection($links),
        ]);
    }

    public function store(CreateShareLinkRequest $request)
    {
        $link = $this->shareService->createShareLink(
            $request->user(),
            $request->input('shareable_type'),
            $request->input('shareable_id'),
            $request->input('expires_in')
        );

        return response()->json([
            'link' => new ShareLinkResource($link),
        ]);
    }

    public function destroy(Request $request, ShareLink $shareLink)
    {
        $this->authorize('delete', $shareLink);

        $shareLink->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
