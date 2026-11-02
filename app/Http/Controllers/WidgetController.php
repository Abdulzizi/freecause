<?php

namespace App\Http\Controllers;

use App\Models\Petition;

class WidgetController extends Controller
{
    private const SIZES = [
        '728x90' => [728, 90],
        '468x60' => [468, 60],
        '336x280' => [336, 280],
        '125x125' => [125, 125],
    ];

    public function script(int $petition, string $type)
    {
        [$width, $height] = self::SIZES[$type] ?? abort(404);
        $src = url("/widget-render/pbadge/{$petition}/{$type}");
        $javascript = "document.write('<iframe src=\"{$src}\" width=\"{$width}\" height=\"{$height}\" style=\"border:0\" frameborder=\"0\" class=\"petition-pbadge-{$type}\"></iframe>');";

        return response($javascript)
            ->header('Content-Type', 'text/javascript; charset=UTF-8')
            ->header('X-Robots-Tag', 'noindex, follow');
    }

    public function render(int $petition, string $type)
    {
        abort_unless(isset(self::SIZES[$type]), 404);

        $petition = Petition::query()
            ->whereKey($petition)
            ->where('status', 'published')
            ->where('is_active', true)
            ->firstOrFail();

        $translation = $petition->translationOrFallback(default_locale());

        abort_if(! $translation, 404);

        return response()
            ->view('petition.widget', compact('petition', 'translation', 'type'))
            ->header('X-Robots-Tag', 'noindex, follow');
    }
}
