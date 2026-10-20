<?php

namespace App\Http\Controllers;

use App\Support\Ads;

class AdsConfigController extends Controller
{
    public function __invoke(string $slot)
    {
        abort_unless(Ads::enabled(), 404);

        $cfg = Ads::slot($slot);

        abort_if($cfg === null || ($cfg['format'] ?? '') === 'native', 404);

        $js = sprintf(
            "atOptions = {'key': %s, 'format': 'iframe', 'height': %d, 'width': %d, 'params': {}};",
            json_encode((string) ($cfg['key'] ?? '')),
            (int) ($cfg['h'] ?? 0),
            (int) ($cfg['w'] ?? 0)
        );

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
