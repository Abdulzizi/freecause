<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;

class LanguageOptionsController extends Controller
{
    protected array $locales = [
        'en' => 'English',
        'fr' => 'French',
        'it' => 'Italian',
    ];

    protected array $homepageKeys = [
        // HERO
        'hero_h1',
        'hero_h2',
        'btn_create_petition',

        // TABS
        'tab_featured',
        'tab_recent',

        // FEATURED
        'featured_badge',
        'featured_none_title',
        'featured_none_sub',
        'read_more',
        'petition_target_label',
        'signatures_label',
        'goal_label',

        // RECENT
        'recent_has_signed',
        'recent_empty',
        'petition_fallback',

        // WHAT SECTION
        'what_title',
        'what_p1',
        'what_p2',
        'what_p3',
        'what_p4',
        'what_link',

        // CREATE BOX
        'create_box_title',
        'create_box_p1',
        'create_box_li1',
        'create_box_li2',
        'create_box_li3',
        'create_box_li4',
        'create_box_li5',
        'create_box_li6',
        'create_box_p2',
        'create_box_link',

        // CATEGORIES + BLOG
        'categories_title',
        'blog_title',
        'blog_subtitle',
        'blog_read_more',
    ];

    public function edit(Request $request)
    {
        $locale = $request->query('locale');

        $showForm = $locale && isset($this->locales[$locale]);

        $values = [];

        if ($showForm) {
            $values = PageContent::where('page', 'home')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
        }

        return view('admin.options.language', [
            'locales' => $this->locales,
            'locale' => $locale,
            'values' => $values,
            'homepageKeys' => $this->homepageKeys,
            'showForm' => $showForm,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'locale' => ['required', 'in:' . implode(',', array_keys($this->locales))],
        ]);

        // $locale = $request->locale;
        $locale = $request->input('locale');

        foreach ($this->homepageKeys as $key) {
            PageContent::updateOrCreate(
                [
                    'page' => 'home',
                    'locale' => $locale,
                    'key' => $key,
                ],
                [
                    'value' => $request->input($key, ''),
                ]
            );
        }

        return redirect()
            ->route('admin.options.language', ['locale' => $locale])
            ->with('success', 'saved');
    }
}
