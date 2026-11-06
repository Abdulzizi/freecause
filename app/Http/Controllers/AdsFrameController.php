<?php

namespace App\Http\Controllers;

use App\Support\Ads;

// Serves one Adsterra slot inside its own same-origin document so the network's
// inline <script> (blocked by the public site's strict CSP) can run in isolation.
// The relaxed CSP for this path is set in AddSecurityHeaders.
class AdsFrameController extends Controller
{
    public function __invoke(string $slot)
    {
        abort_unless(Ads::enabled(), 404);

        abort_unless(Ads::frameHostMatchesRequest(), 404);

        $cfg = Ads::slot($slot);

        abort_if($cfg === null || empty($cfg['key']), 404);

        return response()
            ->view('ads.frame', [
                'slot' => $slot,
                'cfg' => $cfg,
                'host' => Ads::host(),
            ])
            ->header('Cache-Control', 'public, max-age=300, s-maxage=300');
    }
}
