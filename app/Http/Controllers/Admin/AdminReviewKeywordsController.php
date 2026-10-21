<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\Locale;
use App\Support\ReviewKeywords;
use Illuminate\Http\Request;

class AdminReviewKeywordsController extends Controller
{
    public function index(Request $request)
    {
        $catalogue = ReviewKeywords::catalogue();
        $overrides = ReviewKeywords::overrides();

        $locales = Locale::options();

        $languageNames = Language::pluck('name', 'code')->all();

        foreach (array_keys($catalogue) as $code) {
            if (! isset($locales[$code])) {
                $locales[$code] = $languageNames[$code] ?? $code;
            }
        }

        $locale = $request->query('locale');
        $locale = is_string($locale) ? $locale : '';

        if (! isset($locales[$locale])) {
            $locale = default_locale();
        }

        if (! isset($locales[$locale])) {
            $locales[$locale] = $locale;
        }

        $terms = $catalogue[$locale] ?? [];
        $reference = ReviewKeywords::TERMS;
        $hasBundled = $this->hasBundledList($locale);
        $maxTerms = ReviewKeywords::MAX_TERMS;

        $counts = [];

        foreach ($catalogue as $code => $localeTerms) {
            $counts[] = [
                'locale' => $code,
                'name' => $locales[$code] ?? $code,
                'count' => count($localeTerms),
                'custom' => array_key_exists($code, $overrides),
            ];
        }

        return view('admin.keywords.index', compact(
            'locale',
            'locales',
            'terms',
            'reference',
            'overrides',
            'counts',
            'hasBundled',
            'maxTerms'
        ));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string'],
            'terms' => ['sometimes', 'nullable', 'string', 'max:20000'],
        ]);

        $locale = (string) $validated['locale'];

        if (! in_array($locale, $this->allowedLocales(), true)) {
            return redirect()->route('admin.keywords', ['locale' => default_locale()])->with('error', 'Invalid locale.');
        }

        if (! $request->has('terms')) {
            return redirect()->route('admin.keywords', ['locale' => $locale])->with('error', 'Terms field missing.')->withInput();
        }

        $raw = $request->input('terms');
        $raw = is_string($raw) ? $raw : '';
        $lines = $raw === '' ? [] : preg_split('/\R/u', $raw);

        if ($lines === false) {
            return redirect()->route('admin.keywords', ['locale' => $locale])->with('error', 'Invalid text encoding.')->withInput();
        }

        $effective = count(ReviewKeywords::normalizeTerms($lines));

        if ($effective > ReviewKeywords::MAX_TERMS) {
            return redirect()->route('admin.keywords', ['locale' => $locale])->with('error', 'Too many terms — maximum '.ReviewKeywords::MAX_TERMS.' per locale.')->withInput();
        }

        ReviewKeywords::saveLocale($locale, $lines);

        return redirect()
            ->route('admin.keywords', ['locale' => $locale])
            ->with('success', "Keyword list saved for {$locale}.");
    }

    public function reset(Request $request)
    {
        $locale = $request->input('locale');
        $locale = is_string($locale) ? $locale : '';

        if (! in_array($locale, $this->allowedLocales(), true)) {
            return redirect()->route('admin.keywords', ['locale' => default_locale()])->with('error', 'Invalid locale.');
        }

        ReviewKeywords::resetLocale($locale);

        $message = $this->hasBundledList($locale)
            ? "Keyword list reset to the bundled default for {$locale}."
            : "Custom keyword list removed for {$locale}.";

        return redirect()
            ->route('admin.keywords', ['locale' => $locale])
            ->with('success', $message);
    }

    protected function hasBundledList(string $locale): bool
    {
        return $locale === 'en' || array_key_exists($locale, ReviewKeywords::translations());
    }

    /**
     * @return array<int, string>
     */
    protected function allowedLocales(): array
    {
        return array_unique(array_merge(
            array_keys(ReviewKeywords::catalogue()),
            array_keys(Locale::options())
        ));
    }
}
