<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'en';

        if ($request->user()) {
            $locale = $request->user()->locale ?? 'en';
        } elseif ($request->cookie('locale')) {
            $locale = $request->cookie('locale');
        }

        if (!in_array($locale, ['en', 'es'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
