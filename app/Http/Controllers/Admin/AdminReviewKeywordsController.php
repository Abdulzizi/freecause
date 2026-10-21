<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RescanReviewKeywords;
use App\Models\Language;
use App\Support\Locale;
use App\Support\ReviewKeywords;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminReviewKeywordsController extends Controller
{
    private const RESCAN_GUARD_KEY = 'admin.review_keywords_rescan_guard';

    private const RESCAN_GUARD_SECONDS = 30;

    public function index(Request $request)
    {
        $catalogue = ReviewKeywords::catalogue();
        $overrides = ReviewKeywords::overrides();
        $disabledConcepts = ReviewKeywords::disabledConcepts();

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

        $entries = ReviewKeywords::entriesFor($locale);
        $terms = $this->termNames($entries);
        $formatted = ReviewKeywords::formatTerms($entries);
        $reference = ReviewKeywords::TERMS;
        $hasBundled = $this->hasBundledList($locale);
        $maxTerms = ReviewKeywords::MAX_TERMS;
        $unblockedLegacy = ReviewKeywords::unblockedLegacyTerms();
        $status = RescanReviewKeywords::lastStatus();

        $counts = [];

        foreach ($catalogue as $code => $localeTerms) {
            $counts[] = [
                'locale' => $code,
                'name' => $locales[$code] ?? $code,
                'count' => count($localeTerms),
                'custom' => array_key_exists($code, $overrides),
            ];
        }

        $conceptRows = $this->conceptRows($locale, $disabledConcepts);

        return view('admin.keywords.index', compact(
            'locale',
            'locales',
            'terms',
            'formatted',
            'reference',
            'overrides',
            'counts',
            'conceptRows',
            'hasBundled',
            'maxTerms',
            'unblockedLegacy',
            'status'
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

        if ($raw !== '' && preg_split('/\R/u', $raw) === false) {
            return redirect()->route('admin.keywords', ['locale' => $locale])->with('error', 'Invalid text encoding.')->withInput();
        }

        $parsed = ReviewKeywords::parseTerms($raw, null);

        if (count($parsed) > ReviewKeywords::MAX_TERMS) {
            return redirect()->route('admin.keywords', ['locale' => $locale])->with('error', 'Too many terms — maximum '.ReviewKeywords::MAX_TERMS.' per locale.')->withInput();
        }

        ReviewKeywords::saveLocale($locale, $parsed);

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

    public function toggleConcept(Request $request)
    {
        $validated = $request->validate([
            'concept' => ['required', 'string', 'max:255'],
            'enabled' => ['required', 'in:0,1'],
        ]);

        $concept = mb_strtolower(trim((string) $validated['concept']));
        $locale = $this->targetLocale($request);
        $enabled = (int) $validated['enabled'] === 1;

        $known = array_key_exists($concept, ReviewKeywords::concepts());
        $disabled = in_array($concept, ReviewKeywords::disabledConcepts(), true);

        if (! $known && ! ($enabled && $disabled)) {
            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'Unknown concept.');
        }

        if (! ReviewKeywords::setConceptEnabled($concept, $enabled)) {
            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'The concept list could not be updated — the stored value is invalid.');
        }

        return redirect()
            ->route('admin.keywords', ['locale' => $locale])
            ->with('success', $enabled
                ? "Concept \"{$concept}\" enabled."
                : "Concept \"{$concept}\" disabled.");
    }

    public function rescan(Request $request)
    {
        $locale = $this->targetLocale($request);

        if ((string) $request->input('confirm') !== 'rescan') {
            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'Rescan not confirmed.');
        }

        if (! admin_can('petitions', 'edit')) {
            abort(403);
        }

        $lease = RescanReviewKeywords::leaseState();

        if ($lease['running'] && $this->rescanLeaseIsFresh($lease['startedAt'])) {
            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'A rescan is already running.');
        }

        if (config('queue.default') === 'sync') {
            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'The queue is running in sync mode — use: php artisan review-keywords:rescan');
        }

        $guard = Cache::lock(self::RESCAN_GUARD_KEY, self::RESCAN_GUARD_SECONDS);

        if (! $this->rescanGuardAcquired($guard)) {
            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'The rescan could not be queued.');
        }

        try {
            return $this->queueRescan($locale);
        } finally {
            rescue(fn (): bool => $guard->release(), false, report: false);
        }
    }

    /**
     * @return RedirectResponse
     */
    protected function queueRescan(string $locale)
    {
        if ($this->rescanIsQueued()) {
            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'A rescan is already queued.');
        }

        $leaseStart = RescanReviewKeywords::claimLease();

        try {
            RescanReviewKeywords::dispatch(null, 0, $leaseStart);
        } catch (\Throwable $e) {
            RescanReviewKeywords::releaseLease($leaseStart);

            Log::warning('Review keyword rescan could not be queued: '.$e->getMessage());

            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'The rescan could not be queued.');
        }

        if (! $this->rescanOwnsClaim($leaseStart) || ! $this->rescanUniqueLockIsHeld()) {
            RescanReviewKeywords::releaseLease($leaseStart);

            Log::warning('Review keyword rescan could not be queued: the lease or the unique lock was taken over.');

            return redirect()
                ->route('admin.keywords', ['locale' => $locale])
                ->with('error', 'The rescan could not be queued.');
        }

        return redirect()
            ->route('admin.keywords', ['locale' => $locale])
            ->with('success', 'Rescan queued. It will hold published petitions that match the current keyword list.');
    }

    protected function rescanGuardAcquired(Lock $guard): bool
    {
        return rescue(fn (): bool => $guard->get(), false, report: false);
    }

    protected function rescanOwnsClaim(int $leaseStart): bool
    {
        $lease = RescanReviewKeywords::leaseState();

        return $lease['running'] && $lease['startedAt'] === $leaseStart;
    }

    protected function rescanLeaseIsFresh(int $startedAt): bool
    {
        return RescanReviewKeywords::leaseIsFresh($startedAt);
    }

    protected function rescanIsQueued(): bool
    {
        return $this->rescanUniqueLockIsHeld();
    }

    protected function rescanUniqueLockIsHeld(): bool
    {
        $lockKey = UniqueLock::getKey(new RescanReviewKeywords);

        $acquired = rescue(
            fn (): bool => Cache::lock($lockKey, 5)->get(fn (): bool => true),
            true,
            report: false
        );

        return ! $acquired;
    }

    /**
     * @param  array<int, array{term: string, concept: string}|string>  $entries
     * @return array<int, string>
     */
    protected function termNames(array $entries): array
    {
        $names = [];

        foreach ($entries as $entry) {
            $name = is_array($entry) ? (string) ($entry['term'] ?? '') : (string) $entry;

            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    protected function hasBundledList(string $locale): bool
    {
        return $locale === 'en' || array_key_exists($locale, ReviewKeywords::translations());
    }

    /**
     * @param  array<int, string>  $disabledConcepts
     * @return array<int, array{concept: string, wordings: array<string, array<int, string>>, enabled: bool}>
     */
    protected function conceptRows(string $locale, array $disabledConcepts): array
    {
        $rows = [];

        $concepts = ReviewKeywords::concepts();

        foreach ($concepts as $concept => $wordings) {
            $concept = (string) $concept;
            $enabled = ! in_array($concept, $disabledConcepts, true);

            if (! isset($wordings[$locale]) && $enabled) {
                continue;
            }

            $rows[] = [
                'concept' => $concept,
                'wordings' => $wordings,
                'enabled' => $enabled,
            ];
        }

        foreach ($disabledConcepts as $concept) {
            $concept = (string) $concept;

            if (array_key_exists($concept, $concepts)) {
                continue;
            }

            $rows[] = [
                'concept' => $concept,
                'wordings' => [],
                'enabled' => false,
            ];
        }

        return $rows;
    }

    protected function targetLocale(Request $request): string
    {
        $locale = $request->input('locale');
        $locale = is_string($locale) ? $locale : '';

        return in_array($locale, $this->allowedLocales(), true) ? $locale : default_locale();
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
