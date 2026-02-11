<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:en,es',
        ]);

        $locale = $request->input('locale');

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        App::setLocale($locale);

        return redirect()->back()->cookie('locale', $locale, 60 * 24 * 365);
    }
}
