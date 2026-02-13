<?php

namespace App\Http\Controllers;

use App\Http\Resources\OutfitTemplateResource;
use App\Models\OutfitTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OutfitTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = OutfitTemplate::where(function ($q) use ($request) {
            $q->where('is_system', true)
              ->orWhere('user_id', $request->user()->id);
        })->get();

        return Inertia::render('Outfits/Templates', [
            'templates' => OutfitTemplateResource::collection($templates),
        ]);
    }
}
