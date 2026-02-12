<?php

namespace App\Http\Controllers;

use App\Models\PageTranslation;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $locale, string $slug)
    {
        $tr = PageTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return view('pages.show', compact('tr', 'locale'));
    }
}
