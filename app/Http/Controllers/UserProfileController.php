<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    public function show(string $slug, string $id)
    {
        $slug = request()->route('slug');
        $id = (int) request()->route('id');

        $user = User::findOrFail($id);

        $petitionCount = DB::table('petitions')
            ->where('user_id', $id)
            ->where('status', 'published')
            ->count();

        $signatureCount = DB::table('signatures')
            ->where('user_id', $id)
            ->count();

        $recentPetitions = DB::table('petitions as p')
            ->join('petition_translations as pt', function ($j) {
                $j->on('pt.petition_id', '=', 'p.id')
                    ->where('pt.locale', '=', app()->getLocale());
            })
            ->where('p.user_id', $id)
            ->where('p.status', 'published')
            ->select(['p.id', 'pt.title', 'pt.slug', 'p.signature_count', 'p.created_at'])
            ->orderByDesc('p.id')
            ->limit(5)
            ->get();

        $recentSignatures = DB::table('signatures as s')
            ->join('petitions as p', 'p.id', '=', 's.petition_id')
            ->join('petition_translations as pt', function ($j) {
                $j->on('pt.petition_id', '=', 'p.id')
                    ->where('pt.locale', '=', app()->getLocale());
            })
            ->where('s.user_id', $id)
            ->select(['s.id', 'pt.title', 'pt.slug', 'p.id as petition_id', 's.created_at'])
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
