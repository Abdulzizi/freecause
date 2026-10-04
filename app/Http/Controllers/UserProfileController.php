<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    public function show(string $slug, string $id)
    {
        $id = (int) request()->route('id');
        $locale = app()->getLocale();
        $defaultLocale = default_locale();

        // Data is stable; cache 2 min so scraper floods hit Redis instead of MySQL.
        $data = cache()->remember("user:profile:data:{$id}:{$locale}", now()->addSeconds(120), function () use ($id, $locale, $defaultLocale) {
            $user = User::findOrFail($id);

            $petitionCount = 0;
            $signatureCount = 0;
            $recentPetitions = collect();
            $recentSignatures = collect();

            try {
                $petitionCount = DB::table('petitions')
                    ->where('user_id', $id)
                    ->where('status', 'published')
                    ->count();

                $signatureCount = DB::table('signatures')
                    ->where('user_id', $id)
                    ->count();

                $recentPetitions = DB::table('petitions as p')
                    ->select(['p.id', 'p.signature_count', 'p.created_at'])
                    ->withTranslation([
                        'base' => 'p',
                        'base_key' => 'id',
                        'translation_table' => 'petition_translations',
                        'translation_key' => 'petition_id',
                        'locale_alias' => 'pt_locale',
                        'default_alias' => 'pt_default',
                        'locale' => $locale,
                        'default_locale' => $defaultLocale,
                        'columns' => ['title' => 'title', 'slug' => 'slug'],
                    ])
                    ->where('p.user_id', $id)
                    ->where('p.status', 'published')
                    ->whereRaw('(pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)')
                    ->orderByDesc('p.id')
                    ->limit(5)
                    ->get();

                $recentSignatures = DB::table('signatures as s')
                    ->join('petitions as p', 'p.id', '=', 's.petition_id')
                    ->select(['s.id', 'p.id as petition_id', 's.created_at'])
                    ->withTranslation([
                        'base' => 'p',
                        'base_key' => 'id',
                        'translation_table' => 'petition_translations',
                        'translation_key' => 'petition_id',
                        'locale_alias' => 'pt_locale',
                        'default_alias' => 'pt_default',
                        'locale' => $locale,
                        'default_locale' => $defaultLocale,
                        'columns' => ['title' => 'title', 'slug' => 'slug'],
                    ])
                    ->where('s.user_id', $id)
                    ->whereRaw('(pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)')
                    ->orderByDesc('s.id')
                    ->limit(5)
                    ->get();
            } catch (\Throwable $e) {
                $petitionCount = 0;
                $signatureCount = 0;
                $recentPetitions = collect();
                $recentSignatures = collect();
            }

            return compact(
                'user',
                'petitionCount',
                'signatureCount',
                'recentPetitions',
                'recentSignatures'
            );
        });

        return view('pages.user-profile', $data);
    }
}
