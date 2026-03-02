<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFanpagesController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id' => trim((string) $request->query('id', '')),
            'email' => trim((string) $request->query('email', '')),
            'title' => trim((string) $request->query('title', '')),
        ];

        $q = DB::table('petitions')
            ->join('users', 'users.id', '=', 'petitions.user_id')
            ->join('petition_translations as pt', function ($j) use ($locale) {
                $j->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->select([
                'petitions.id',
                'users.email',
                'pt.title',
                'pt.locale',
                'petitions.created_at',
            ]);

        if ($filters['id'] !== '') {
            $q->where('petitions.id', (int) $filters['id']);
        }

        if ($filters['email'] !== '') {
            $q->where('users.email', 'like', '%' . $filters['email'] . '%');
        }

        if ($filters['title'] !== '') {
            $q->where('pt.title', 'like', '%' . $filters['title'] . '%');
        }

        $q->orderByDesc('petitions.id');

        $fanpages = $q->paginate(50)->withQueryString();

        $approxTotal = DB::table('petitions')->count();

        // BUG 3 FIX: dynamic locales from languages table instead of hardcoded
        $languages = Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $locales = ['' => '(Locale)'];
        foreach ($languages as $lang) {
            $locales[$lang->code] = $lang->name;
        }

        return view('admin.fanpages.index', compact(
            'fanpages',
            'filters',
            'approxTotal',
            'locale',
            'locales'
        ));
    }
}
