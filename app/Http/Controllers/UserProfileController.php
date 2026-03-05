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

        $user = User::findOrFail($id);

        $petitionCount = DB::table('petitions')
            ->where('user_id', $id)
            ->where('status', 'published')
            ->count();

        $signatureCount = DB::table('signatures')
            ->where('user_id', $id)
            ->count();

        $recentPetitions = DB::table('petitions as p')
            ->leftJoin('petition_translations as pt_locale', function ($j) use ($locale) {
                $j->on('pt_locale.petition_id', '=', 'p.id')
                    ->where('pt_locale.locale', '=', $locale);
            })
            ->leftJoin('petition_translations as pt_default', function ($j) use ($defaultLocale) {
                $j->on('pt_default.petition_id', '=', 'p.id')
                    ->where('pt_default.locale', '=', $defaultLocale);
            })
            ->where('p.user_id', $id)
            ->where('p.status', 'published')
            ->whereRaw('(pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)')
            ->select([
                'p.id',
                DB::raw('COALESCE(pt_locale.title, pt_default.title) as title'),
                DB::raw('COALESCE(pt_locale.slug, pt_default.slug) as slug'),
                'p.signature_count',
                'p.created_at',
            ])
            ->orderByDesc('p.id')
            ->limit(5)
            ->get();

        $recentSignatures = DB::table('signatures as s')
            ->join('petitions as p', 'p.id', '=', 's.petition_id')
            ->leftJoin('petition_translations as pt_locale', function ($j) use ($locale) {
                $j->on('pt_locale.petition_id', '=', 'p.id')
                    ->where('pt_locale.locale', '=', $locale);
            })
            ->leftJoin('petition_translations as pt_default', function ($j) use ($defaultLocale) {
                $j->on('pt_default.petition_id', '=', 'p.id')
                    ->where('pt_default.locale', '=', $defaultLocale);
            })
            ->where('s.user_id', $id)
            ->whereRaw('(pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)')
            ->select([
                's.id',
                DB::raw('COALESCE(pt_locale.title, pt_default.title) as title'),
                DB::raw('COALESCE(pt_locale.slug, pt_default.slug) as slug'),
                'p.id as petition_id',
                's.created_at',
            ])
            ->orderByDesc('s.id')
            ->limit(5)
            ->get();

        return view('pages.user-profile', compact(
            'user',
            'petitionCount',
            'signatureCount',
            'recentPetitions',
            'recentSignatures'
        ));
    }
}
