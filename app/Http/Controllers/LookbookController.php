<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLookbookRequest;
use App\Http\Requests\UpdateLookbookRequest;
use App\Http\Resources\LookbookResource;
use App\Models\Lookbook;
use App\Models\LookbookItem;
use App\Services\LookbookService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LookbookController extends Controller
{
    public function __construct(
        private LookbookService $lookbookService
    ) {}

    public function index(Request $request)
    {
        $lookbooks = $request->user()
            ->lookbooks()
            ->withCount('items')
            ->latest()
            ->get();

        return Inertia::render('Lookbooks/Index', [
            'lookbooks' => LookbookResource::collection($lookbooks),
        ]);
    }

    public function store(StoreLookbookRequest $request)
    {
        $this->lookbookService->createLookbook(
            $request->user(),
            $request->validated()
        );

        return redirect()->back()->with('success', __('messages.lookbook_created'));
    }

    public function show(Request $request, Lookbook $lookbook)
    {
        $this->authorize('view', $lookbook);

        $lookbook->load(['items' => function ($q) {
            $q->orderBy('sort_order')->with('itemable');
        }]);

        return Inertia::render('Lookbooks/Show', [
            'lookbook' => new LookbookResource($lookbook),
        ]);
    }

    public function update(UpdateLookbookRequest $request, Lookbook $lookbook)
    {
        $this->authorize('update', $lookbook);

        $lookbook->update($request->validated());

        return redirect()->back()->with('success', __('messages.lookbook_updated'));
    }

    public function destroy(Request $request, Lookbook $lookbook)
    {
        $this->authorize('delete', $lookbook);

        $lookbook->delete();

        return redirect()->route('lookbooks.index')->with('success', __('messages.lookbook_deleted'));
    }

    public function addItem(Request $request, Lookbook $lookbook)
    {
        $this->authorize('update', $lookbook);

        $request->validate([
            'itemable_type' => 'required|string|in:tryon_result,outfit_suggestion',
            'itemable_id' => 'required|integer',
            'note' => 'nullable|string|max:500',
        ]);

        $this->lookbookService->addItem(
            $lookbook,
            $request->input('itemable_type'),
            $request->input('itemable_id'),
            $request->input('note')
        );

        return redirect()->back()->with('success', __('messages.item_added'));
    }

    public function removeItem(Request $request, Lookbook $lookbook, LookbookItem $item)
    {
        $this->authorize('update', $lookbook);

        // Verify item belongs to this lookbook (prevent cross-tenant deletion)
        if ($item->lookbook_id !== $lookbook->id) {
            abort(404);
        }

        $item->delete();

        return redirect()->back()->with('success', __('messages.item_removed'));
    }

    public function reorder(Request $request, Lookbook $lookbook)
    {
        $this->authorize('update', $lookbook);

        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer',
        ]);

        $this->lookbookService->reorderItems($lookbook, $request->input('item_ids'));

        return response()->json(['success' => true]);
    }
}
