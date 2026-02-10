<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\Request;

class CountryOptionsController extends Controller
{
    public function edit(Request $request)
    {
        $locales = [
            'da_DK' => 'Denmark',
            'fr_FR' => 'France',
            'it_IT' => 'Italy',
            'en_US' => 'United States',
        ];

        $locale = (string) $request->query('locale', '');
        $showForm = ($locale !== '') && isset($locales[$locale]);

        // prev/next only make sense when a locale is selected
        $codes = array_keys($locales);
        $idx = array_search($locale, $codes, true);

        if ($idx === false) {
            $prev = $codes[0];
            $next = $codes[0];
        } else {
            $prev = $codes[max(0, $idx - 1)];
            $next = $codes[min(count($codes) - 1, $idx + 1)];
        }

        // default empty values
        $values = [
            'meta_keywords' => '',
            'meta_description' => '',
            'head_additional_html' => '',
            'h1' => '',
            'h2' => '',
            'text_index_left' => '',
            'text_index_right' => '',
            'footer' => '',
            'exclude_most_read' => '',
        ];

        if ($showForm) {
            $group = "country:$locale";

            $values = [
                'meta_keywords' => Settings::get('meta_keywords', '', $group),
                'meta_description' => Settings::get('meta_description', '', $group),
                'head_additional_html' => Settings::get('head_additional_html', '', $group),

                'h1' => Settings::get('h1', '', $group),
                'h2' => Settings::get('h2', '', $group),
                'text_index_left' => Settings::get('text_index_left', '', $group),
                'text_index_right' => Settings::get('text_index_right', '', $group),
                'footer' => Settings::get('footer', '', $group),
                'exclude_most_read' => Settings::get('exclude_most_read', '', $group),
            ];
        }

        return view('admin.options.country', compact(
            'locales',
            'locale',
            'prev',
            'next',
            'values',
            'showForm'
        ));
    }

    public function update(Request $request)
    {
        $locales = [
            'da_DK' => 'Denmark',
            'fr_FR' => 'France',
            'it_IT' => 'Italy',
            'en_US' => 'United States',
        ];

        $data = $request->validate([
            'locale' => ['required', 'in:' . implode(',', array_keys($locales))],

            'meta_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'head_additional_html' => ['nullable', 'string'],

            'h1' => ['nullable', 'string'],
            'h2' => ['nullable', 'string'],
            'text_index_left' => ['nullable', 'string'],
            'text_index_right' => ['nullable', 'string'],
            'footer' => ['nullable', 'string'],
            'exclude_most_read' => ['nullable', 'string'],
        ]);

        $locale = $data['locale'];
        $group = "country:$locale";

        Settings::set('meta_keywords', $data['meta_keywords'] ?? '', 'text', $group);
        Settings::set('meta_description', $data['meta_description'] ?? '', 'text', $group);
        Settings::set('head_additional_html', $data['head_additional_html'] ?? '', 'text', $group);

        Settings::set('h1', $data['h1'] ?? '', 'text', $group);
        Settings::set('h2', $data['h2'] ?? '', 'text', $group);
        Settings::set('text_index_left', $data['text_index_left'] ?? '', 'text', $group);
        Settings::set('text_index_right', $data['text_index_right'] ?? '', 'text', $group);
        Settings::set('footer', $data['footer'] ?? '', 'text', $group);
        Settings::set('exclude_most_read', $data['exclude_most_read'] ?? '', 'string', $group);

        return redirect()
            ->route('admin.options.country', ['locale' => $locale])
            ->with('success', 'saved');
    }
}
