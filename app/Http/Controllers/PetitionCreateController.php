<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use App\Models\Signature;
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
            'title' => [
                'required',
                'string',
                'max:190',
                function ($attr, $value, $fail) {
                    $words = preg_split('/\s+/', trim((string) $value));
                    $words = array_values(array_filter($words, fn($w) => $w !== ''));
                    if (count($words) < 3) {
                        $fail('Title must contain at least 3 words.');
                    }
                },
            ],

            'description' => ['required', 'string', 'min:30'],

            'goal_signatures' => ['required', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'tags' => [
                'nullable',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    $tags = collect(explode(',', (string) $value))
                        ->map(fn($t) => trim($t))
                        ->filter()
                        ->values();

                    if ($tags->count() > 10) {
                        $fail('Tags: maximum 10 keywords.');
                    }

                    // optional: prevent very long single tags
                    if ($tags->contains(fn($t) => mb_strlen($t) > 30)) {
                        $fail('Tags: each keyword must be 30 characters or less.');
                    }
                },
            ],

            'image' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:500'],

            'youtube' => ['nullable', 'url', 'max:200'],

            'target' => ['nullable', 'string', 'max:190'],
            'community' => ['nullable', 'string', 'max:190'],
            'community_url' => ['nullable', 'url', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
        ], [
            'image.prohibited_with' => 'Please choose either upload an image OR use an external image link (not both).',
            'image_url.prohibited_with' => 'Please choose either upload an image OR use an external image link (not both).',
        ]);

        if ($request->hasFile('image') && filled($data['image_url'] ?? null)) {
            return back()
                ->withErrors([
                    'image' => 'Please choose either upload an image OR use an external image link (not both).',
                    'image_url' => 'Please choose either upload an image OR use an external image link (not both).',
                ])
                ->withInput();
        }


        $tags = collect(explode(',', $data['tags'] ?? ''))
            ->map(fn($t) => trim($t))
            ->filter()
            ->take(10)
            ->implode(',');

        $baseSlug = Str::slug($data['title']);
        $slug = $baseSlug;
        $i = 1;
        while (Petition::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $petition = new Petition();
        $petition->user_id = auth()->id();
        $petition->locale = $locale;
        $petition->status = 'draft';
        $petition->title = $data['title'];
        $petition->slug = $slug;
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

        $user = auth()->user();

        $alreadySigned = Signature::query()
            ->where('petition_id', $petition->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->exists();

        if (! $alreadySigned) {
            Signature::create([
                'petition_id' => $petition->id,
                'user_id' => $user->id,
                'name' => $user->name ?? 'Anonymous',
                'email' => $user->email,
                'locale' => $locale,
            ]);

            $petition->increment('signature_count');
        }

        return redirect()->route('petition.thanks', [
            'locale' => $locale,
            'slug' => $petition->slug,
            'id' => $petition->id,
            'mode' => 'created',
        ]);
    }
}
