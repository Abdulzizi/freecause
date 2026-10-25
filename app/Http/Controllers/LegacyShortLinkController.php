<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LegacyShortLinkController extends Controller
{
    public function __invoke(string $shortLink): RedirectResponse
    {
        $shortLink = strtoupper($shortLink);

        $target = Cache::remember(
            'legacy_shortlink:'.$shortLink,
            now()->addDay(),
            fn () => DB::table('legacy_shortlinks as l')
                ->join('petition_translations as t', 't.petition_id', '=', 'l.petition_id')
                ->where('l.short_link', $shortLink)
                ->orderBy('t.id')
                ->select('t.locale', 't.slug', 'l.petition_id')
                ->first()
        );

        if (! $target) {
            return redirect('/'.default_locale(), 302);
        }

        return redirect("/{$target->locale}/petition/{$target->slug}/{$target->petition_id}", 301);
    }
}
