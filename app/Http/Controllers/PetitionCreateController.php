<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PetitionCreateController extends Controller
{
    public function create(string $locale)
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('petition.create', compact('locale', 'categories'));
    }

    public function store(Request $request, string $locale)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['required', 'string'],
            'goal_signatures' => ['required', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'tags' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'youtube' => ['nullable', 'string', 'max:200'],

            'target' => ['nullable', 'string', 'max:190'],
            'community' => ['nullable', 'string', 'max:190'],
            'community_url' => ['nullable', 'url', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        // tags cleanup (max 10)
        $tags = collect(explode(',', $data['tags'] ?? ''))
            ->map(fn($t) => trim($t))
            ->filter()
            ->take(10)
            ->implode(',');

        $petition = new Petition();
        $petition->user_id = auth()->id();
        $petition->locale = $locale;
        $petition->status = 'draft'; // phase 1
        $petition->title = $data['title'];
        $petition->slug = Str::slug($data['title']);
        $petition->description = $data['description'];
        $petition->goal_signatures = $data['goal_signatures'];
        $petition->category_id = $data['category_id'];
        $petition->target = $data['target'] ?? null;
        $petition->tags = $tags ?: null;
        $petition->city = $data['city'] ?? null;

        $petition->community = $data['community'] ?? null;
        $petition->community_url = $data['community_url'] ?? null;
        $petition->youtube_url = $data['youtube'] ?? null;
        $petition->image_url = $data['image_url'] ?? null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('petitions', 'public');
            $petition->cover_image = $path;
        }

        $petition->save();

        return redirect()->route('petition.thanks', [
            'locale' => $locale,
            'slug' => $petition->slug,
            'id' => $petition->id,
            'status' => 'created',
        ]);
    }
}
