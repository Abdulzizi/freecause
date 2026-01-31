<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\Signature;
use Illuminate\Http\Request;

class PetitionController extends Controller
{
    public function index(string $locale)
    {
        $petitions = Petition::query()
            ->where('locale', $locale)
            ->where('status', 'published')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => 'All the petitions',
            'heading' => 'Petitions',
            'petitions' => $petitions,
            'petitionTitle' => fn($p) => $p->title,
            'petitionUrl' => fn($p) => url("/{$locale}/petition/{$p->slug}/{$p->id}"),
        ]);
    }

    public function show(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()
            ->where('id', $id)
            ->where('locale', $locale)
            ->with('category')
            ->firstOrFail();

        if ($petition->slug !== $slug) {
            return redirect()->route('petition.show', [
                'locale' => $locale,
                'slug' => $petition->slug,
                'id' => $petition->id,
            ]);
        }

        $goalTotal = (int) ($petition->goal_signatures ?? 100);
        $goalCurrent = (int) ($petition->signature_count ?? 0);
        $pct = $goalTotal > 0 ? min(100, round(($goalCurrent / $goalTotal) * 100)) : 0;

        $latest = Signature::query()
            ->where('petition_id', $petition->id)
            ->latest('created_at')
            ->limit(25)
            ->get();

        $directLink = url("/{$locale}/petition/{$petition->slug}/{$petition->id}");

        return view('petition.show', compact(
            'locale',
            'petition',
            'goalTotal',
            'goalCurrent',
            'pct',
            'latest',
            'directLink'
        ));
    }
}
