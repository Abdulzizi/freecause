<?php

namespace App\Http\Controllers;

use App\Models\PageTranslation;
use Illuminate\Http\RedirectResponse;

class PageController extends Controller
{
    public function show(string $locale, string $slug)
    {
        return $this->renderBySlug($locale, $slug);
    }

    public function showFixed(string $locale, string $slug)
    {
        if (request()->has('status')) {
            $query = request()->query();
            unset($query['status']);
            $target = '/'.ltrim(request()->path(), '/').($query !== [] ? '?'.http_build_query($query) : '');

            return new RedirectResponse($target, 301);
        }

        return $this->renderBySlug($locale, $slug);
    }

    private function renderBySlug(string $locale, string $slug)
    {
        $defaultLocale = default_locale();

        $tr = PageTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->where('published', true)
            ->first();

        $fallback = false;

        if (! $tr) {
            $tr = PageTranslation::query()
                ->where('locale', $defaultLocale)
                ->where('slug', $slug)
                ->where('published', true)
                ->first();

            $fallback = $tr !== null && $locale !== $defaultLocale;
        }

        if (! $tr) {
            abort(404);
        }

        return view('pages.show', compact('tr', 'locale', 'fallback', 'defaultLocale'));
    }
}
