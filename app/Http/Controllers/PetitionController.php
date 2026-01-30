<?php

namespace App\Http\Controllers;

use App\Models\Petition;

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

    public function show(string $locale, string $slug, int $id)
    {
        $petition = Petition::query()
            ->where('id', $id)
            ->where('locale', $locale)
            ->where('status', 'published')
            ->firstOrFail();

        if ($petition->slug !== $slug) {
            return redirect()->to(lroute('petition.show', ['slug' => $petition->slug, 'id' => $petition->id]));
        }

        return view('petition.show', compact('petition'));
    }
}
