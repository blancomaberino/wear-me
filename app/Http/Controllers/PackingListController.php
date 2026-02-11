<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackingListRequest;
use App\Http\Resources\PackingListResource;
use App\Http\Resources\GarmentResource;
use App\Models\PackingList;
use App\Services\PackingListService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PackingListController extends Controller
{
    public function __construct(
        private PackingListService $packingListService
    ) {}

    public function index(Request $request)
    {
        $lists = $request->user()
            ->packingLists()
            ->withCount(['items', 'items as packed_items_count' => function ($q) {
                $q->where('is_packed', true);
            }])
            ->latest()
            ->get();

        return Inertia::render('PackingLists/Index', [
            'packingLists' => PackingListResource::collection($lists),
        ]);
    }

    public function store(StorePackingListRequest $request)
    {
        $list = $this->packingListService->createPackingList(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('packing-lists.show', $list)->with('success', __('messages.packing_list_created'));
    }

    public function show(Request $request, PackingList $packingList)
    {
        $this->authorize('view', $packingList);

        $packingList->load(['items.garment']);
        $garments = $request->user()->garments()->latest()->get();

        return Inertia::render('PackingLists/Show', [
            'packingList' => new PackingListResource($packingList),
            'garments' => GarmentResource::collection($garments),
        ]);
    }

    public function update(StorePackingListRequest $request, PackingList $packingList)
    {
        $this->authorize('update', $packingList);

        $packingList->update($request->validated());

        return redirect()->back()->with('success', __('messages.packing_list_updated'));
    }

    public function destroy(Request $request, PackingList $packingList)
    {
        $this->authorize('delete', $packingList);

        $packingList->delete();

        return redirect()->route('packing-lists.index')->with('success', __('messages.packing_list_deleted'));
    }

    public function addItem(Request $request, PackingList $packingList)
    {
        $this->authorize('update', $packingList);

        $request->validate([
            'garment_id' => [
                'required', 'integer',
                Rule::exists('garments', 'id')->where('user_id', $request->user()->id),
            ],
            'day_number' => 'nullable|integer|min:1',
            'occasion' => 'nullable|string|max:100',
        ]);

        $this->packingListService->addItem(
            $packingList,
            $request->input('garment_id'),
            $request->input('day_number'),
            $request->input('occasion')
        );

        return redirect()->back()->with('success', __('messages.item_added'));
    }

    public function removeItem(Request $request, PackingList $packingList, int $item)
    {
        $this->authorize('update', $packingList);

        $packingList->items()->where('id', $item)->delete();

        return redirect()->back()->with('success', __('messages.item_removed'));
    }

    public function togglePacked(Request $request, PackingList $packingList, int $item)
    {
        $this->authorize('update', $packingList);

        $isPacked = $this->packingListService->togglePacked($packingList, $item);

        return response()->json(['is_packed' => $isPacked]);
    }
}
