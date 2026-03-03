<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\PageContent;
use App\Services\PageContentService;
use Illuminate\Http\Request;

class LanguageOptionsController extends Controller
{
    protected array $homepageKeys = [
        'meta_keywords',
        'meta_description',
        'head_additional_html',
        'h1',
        'h2',
        'btn_create_petition',
        'tab_featured',
        'tab_recent',
        'featured_badge',
        'featured_none_title',
        'featured_none_sub',
        'petition_target_label',
        'signatures_label',
        'goal_label',
        'read_more',
        'recent_has_signed',
        'recent_empty',
        'text_index_left',
        'text_index_right',
        'exclude_most_read',
    ];

    protected array $layoutKeys = [
        'footer_about',
        'footer_links',
        'footer_bottom',
    ];

    private function getLocales(): array
    {
        return Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->pluck('name', 'code')
            ->toArray();
    }

    public function edit(Request $request)
    {
        $locales = $this->getLocales();

        $locale = $request->query('locale');
        $showForm = $locale && isset($locales[$locale]);

        $values = [];
        $layoutValues = [];

        if ($showForm) {
            $values = PageContent::where('page', 'home')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();

            $layoutValues = PageContent::where('page', 'layout')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
        }

        return view('admin.options.language', [
            'locales'      => $locales,
            'locale'       => $locale,
            'values'       => $values,
            'layoutValues' => $layoutValues,
            'showForm'     => $showForm,
        ]);
    }

    public function update(Request $request)
    {
        $locales = $this->getLocales();

        $request->validate([
            'locale' => ['required', 'in:' . implode(',', array_keys($locales))],
        ]);

        $locale = $request->input('locale');

        foreach ($this->homepageKeys as $key) {
            PageContent::updateOrCreate(
                [
                    'page'   => 'home',
                    'locale' => $locale,
                    'key'    => $key,
                ],
                [
                    'value' => $request->input($key, ''),
                ]
            );
        }

        foreach ($this->layoutKeys as $key) {
            PageContent::updateOrCreate(
                [
                    'page'   => 'layout',
                    'locale' => $locale,
                    'key'    => $key,
                ],
                [
                    'value' => $request->input($key, ''),
                ]
            );
        }

        app(PageContentService::class)->clear('home', $locale);
        app(PageContentService::class)->clear('layout', $locale);

        return redirect()
            ->route('admin.options.language', ['locale' => $locale])
            ->with('success', 'saved');
    }
}
