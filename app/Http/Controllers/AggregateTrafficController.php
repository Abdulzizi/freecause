<?php

namespace App\Http\Controllers;

use App\Support\AggregateTraffic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AggregateTrafficController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $origin = $request->header('Origin');
        $allowed = rtrim((string) config('app.url'), '/');
        abort_unless(is_string($origin) && $origin === $allowed, 403);
        abort_if($request->header('Sec-Fetch-Site') && $request->header('Sec-Fetch-Site') !== 'same-origin', 403);

        if (! config('aggregate-traffic.enabled') || $request->header('DNT') === '1' || $request->header('Sec-GPC') === '1') {
            return $this->empty();
        }

        // Never accept URLs, referrers, identifiers or arbitrary dimension values.
        if (strlen($request->getContent()) > 256 || ! $request->isJson()) {
            return $this->empty();
        }
        $data = $request->json()->all();
        if (array_diff(array_keys($data), ['area', 'locale', 'device', 'automated'])
            || ! in_array($data['area'] ?? null, AggregateTraffic::AREAS, true)
            || ! in_array($data['locale'] ?? null, AggregateTraffic::LOCALES, true)
            || ! in_array($data['device'] ?? null, AggregateTraffic::DEVICES, true)
            || ! is_bool($data['automated'] ?? null)) {
            return $this->empty();
        }

        $kind = $data['automated'] || AggregateTraffic::knownAutomation($request->userAgent() ?? '') ? 'automation' : 'browser';
        DB::table('aggregate_traffic')->upsert([
            'hour' => now('UTC')->startOfHour()->format('Y-m-d H:i:s'),
            'area' => $data['area'], 'locale' => $data['locale'],
            'device' => $data['device'], 'kind' => $kind, 'page_loads' => 1,
        ], ['hour', 'area', 'locale', 'device', 'kind'], [
            'page_loads' => DB::raw('page_loads + 1'),
        ]);

        return $this->empty();
    }

    private function empty(): Response
    {
        return response('', 204, ['Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, nofollow']);
    }
}
