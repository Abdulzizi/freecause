<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CacheVersion;
use App\Support\EdgeCachePurge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminPetitionDeleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:petitions,id'],
            'confirmed' => ['required', 'accepted'],
        ]);

        [$deleted, $shortLinks, $urls] = DB::transaction(function () use ($data) {
            $ids = DB::table('petitions')->whereIn('id', $data['ids'])
                ->orderBy('id')->lockForUpdate()->pluck('id')->all();
            $shortLinks = DB::table('legacy_shortlinks')->whereIn('petition_id', $ids)->pluck('short_link')->all();
            $urls = EdgeCachePurge::petitionUrls($ids);

            // Legacy short links have no foreign key; the remaining children cascade.
            DB::table('legacy_shortlinks')->whereIn('petition_id', $ids)->delete();
            $deleted = DB::table('petitions')->whereIn('id', $ids)->delete();

            return [$deleted, $shortLinks, $urls];
        });

        try {
            foreach ($shortLinks as $shortLink) {
                Cache::forget('legacy_shortlink:'.strtoupper($shortLink));
            }
            CacheVersion::bumpAll();
            EdgeCachePurge::urls($urls);
        } catch (\Throwable $e) {
            Log::warning('Cache invalidation failed after petition deletion: '.$e->getMessage());
        }

        Log::info('Admin permanently deleted petitions.', [
            'admin_id' => $request->user('admin')?->id,
            'petition_ids' => $data['ids'],
            'deleted' => $deleted,
        ]);

        return response()->json(['ok' => true, 'deleted' => $deleted, 'restorable' => false, 'msg' => $deleted.' petitions permanently deleted.']);
    }
}
