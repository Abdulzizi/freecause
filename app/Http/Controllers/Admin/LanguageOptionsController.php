<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use App\Services\PageContentService;
use Illuminate\Http\Request;

class LanguageOptionsController extends Controller
{
    protected array $locales = [
        'en' => 'English',
        'fr' => 'French',
        'it' => 'Italian',
    ];

    protected array $homepageKeys = [

        // head
        'meta_keywords',
        'meta_description',
        'head_additional_html',

        // hero
        'h1',
        'h2',
        'btn_create_petition',

        // tabs
        'tab_featured',
        'tab_recent',

        // featured
        'featured_badge',
        'featured_none_title',
        'featured_none_sub',
        'petition_target_label',
        'signatures_label',
        'goal_label',
        'read_more',

        // recent
        'recent_has_signed',
        'recent_empty',

        // index text
        'text_index_left',
        'text_index_right',

        // extra
        'exclude_most_read',
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
            'showForm' => $showForm,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'locale' => ['required', 'in:' . implode(',', array_keys($this->locales))],
        ]);

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

        app(PageContentService::class)->clear('home', $locale);
        // app(PageContentService::class)->clear('global', $locale);

        return redirect()
            ->route('admin.options.language', ['locale' => $locale])
            ->with('success', 'saved');
    }
}
