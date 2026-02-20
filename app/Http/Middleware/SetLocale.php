<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale');
        $allowedLocales = cache()->remember('active_languages', 60, fn() => Language::where('is_active', 1)->pluck('code')->toArray());
        $defaultLocale = cache()->remember('default_language', 60, fn() => Language::where('is_default', 1)->value('code') ?? 'en');

        // if (! in_array($locale, $allowedLocales)) {
        //     $locale = $defaultLocale;
        // }

        if (! in_array($locale, $allowedLocales)) {
            return redirect("/{$defaultLocale}");
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        session(['locale' => $locale]);

        return $next($request);
    }
}
