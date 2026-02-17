<?php

namespace App\Http\Controllers;

use App\Models\PageTranslation;

class PageController extends Controller
{
    public function show(string $locale, string $slug)
    {
        $tr = PageTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->where('published', true)
            ->first();

        if (!$tr) {
            $tr = PageTranslation::query()
                ->where('locale', 'en')
                ->where('slug', $slug)
                ->where('published', true)
                ->firstOrFail();
        }

        return view('pages.show', compact('tr', 'locale'));
    }
}
