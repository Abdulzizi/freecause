<?php

namespace App\Http\Controllers;

use App\Models\PageTranslation;

class PageController extends Controller
{
    public function show(string $locale, string $slug)
    {
        $defaultLocale = default_locale();

        $tr = PageTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->where('published', true)
            ->first();

        if (!$tr) {
            $tr = PageTranslation::query()
                ->where('locale', $defaultLocale)
                ->where('slug', $slug)
                ->where('published', true)
                ->first();
        }

        if (!$tr) {
            abort(404);
        }

        return view('pages.show', compact('tr', 'locale'));
    }
}
