<?php

namespace App\Http\Controllers;

use App\Support\Countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    public function cities(Request $request): JsonResponse
    {
        $input = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
            'country' => ['required', 'string', 'size:2'],
        ]);
        abort_unless(Countries::isValid($input['country']), 422);

        $query = mb_strtolower(trim($input['q']));
        $country = strtoupper($input['country']);
        $locale = app()->getLocale();
        $key = 'geo:cities:'.sha1($locale.'|'.$country.'|'.$query);
        $cached = Cache::get($key);
        if ($cached !== null) {
            return response()->json($cached);
        }
        $response = Http::acceptJson()
            ->withUserAgent(config('app.name').'/'.config('app.version', '1.0').' ('.config('app.url').')')
            ->timeout(3)
            ->get('https://photon.komoot.io/api/', [
                'q' => $query,
                'limit' => 6,
                'countrycode' => $country,
                'layer' => 'city',
                'lang' => $locale,
            ]);

        if (! $response->successful()) {
            return response()->json([]);
        }

        $cities = collect($response->json('features', []))
            ->filter(fn (array $feature) => strtoupper($feature['properties']['countrycode'] ?? '') === $country)
            ->map(fn (array $feature) => $feature['properties']['city'] ?? $feature['properties']['name'] ?? null)
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->unique()
            ->take(6)
            ->values();
        Cache::put($key, $cities, 86400);

        return response()->json($cities);
    }
}
