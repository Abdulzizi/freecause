<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Language;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Legacy URL word compatibility layer.
 *
 * Global middleware (runs before the router, so it also covers deep paths that
 * match no {locale} group route). Only acts when the first path segment is a
 * short new-app locale or a legacy full locale literal. Translates legacy route
 * words into the canonical English word paths of the new app, resolves legacy
 * category ids (id -> new id is `(id - 1) % 22 + 1`, the legacy per-prefix id
 * ranges tile 1..726 contiguously in old_id order) and issues permanent 301
 * redirects while preserving query strings.
 */
class LegacyWordRedirect
{
    /**
     * Legacy words per route family. `canonical` entries are words that also
     * exist as new-app route words (matched for every locale when no locale
     * specific word matches) and generally pass through untouched unless their
     * tail needs rewriting. Families intentionally absent here (`feed`,
     * `signatures`, `referers`, `invite-friends`, `tag`, `pic`,
     * `download-signatures`) fall through to the router's 404 — deliberate
     * per LEGACY_URL_MAPPING.md §5/§6/§7.
     */
    private const WORDS = [
        'list' => [
            'canonical' => ['petitions'],
            'da' => ['petitioner'],
            'de' => ['petitionen'],
            'es' => ['peticiones'],
            'it' => ['petizioni'],
            'nl' => ['petities'],
            'pl' => ['petycje'],
            'pt' => ['peticoes'],
            'ro' => ['petitii'],
            'sv' => ['petitioner'],
            'tr' => ['dilekceler'],
        ],
        'petition' => [
            'canonical' => ['petition'],
            'es' => ['peticion'],
            'it' => ['petizione'],
            'nl' => ['petitie'],
            'pl' => ['petycja'],
            'pt' => ['peticao'],
            'ro' => ['petitie'],
            'tr' => ['dilekce'],
        ],
        'user' => [
            'canonical' => ['user'],
            'da' => ['bruger'],
            'de' => ['benutzer'],
            'es' => ['usuario'],
            'fr' => ['utilisateur'],
            'it' => ['utente'],
            'nl' => ['gebruiker'],
            'pl' => ['użytkownik'],
            'pt' => ['ususario'],
            'ro' => ['utente'],
            'sv' => ['anvandare'],
            'tr' => ['kullanici'],
        ],
        'search' => [
            'canonical' => ['search'],
            'da' => ['sog'],
            'de' => ['suchen'],
            'es' => ['busca'],
            'fr' => ['recherchez'],
            'it' => ['cerca'],
            'nl' => ['zoek'],
            'pl' => ['szukaj'],
            'pt' => ['busca'],
            'ro' => ['cauta'],
            'sv' => ['sok'],
            'tr' => ['ara'],
        ],
        'faq' => [
            // NOTE: no `canonical` — `/xx/faqs` is already the real route word and
            // must pass through instead of looping.
            'en' => ['faq'],
            'da' => ['faq'],
            'de' => ['haufig-gestellte-fragen'],
            'es' => ['preguntas-frecuentes'],
            'fr' => ['questions-frequentes'],
            'it' => ['faq'],
            'nl' => ['faq'],
            'pl' => ['najczesciej-zadawane-pytania'],
            'pt' => ['perguntas-frequentes'],
        ],
        'blog' => [
            'en' => ['news'],
            'it' => ['notizie'],
        ],
        'topics' => ['canonical' => ['topics']],
        'results' => ['canonical' => ['results']],
        'feed' => ['canonical' => ['feed']],
        'tag' => [
            'canonical' => ['tag'],
            'es' => ['etiqueta'],
            'ro' => ['cuvinte-cheie'],
            'sv' => ['etikett'],
        ],
        'pic' => [
            'canonical' => ['pic'],
            'de' => ['bild'],
            'sv' => ['bild'],
        ],
        'signatures' => ['canonical' => ['signatures']],
        'referers' => ['canonical' => ['referers']],
        'invite' => ['canonical' => ['invite-friends']],
        'download' => ['canonical' => ['download-signatures']],
        'recover' => ['en' => ['recover-password']],
    ];

    /** Legacy category words, used only to recognise category tail segments. */
    private const CATEGORY = [
        'category', 'categoria', 'categorie', 'kategori', 'kategorie', 'kategoriler', 'kategoria',
    ];

    /** Legacy page words, used only to recognise `page-N` tail segments. */
    private const PAGE = [
        'page', 'pagina', 'side', 'seite', 'strona', 'sida', 'sayfa',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('get')) {
            return $next($request);
        }

        $segments = array_values(array_filter(explode('/', $request->path()), static fn ($s) => $s !== ''));
        $first = $segments[0] ?? null;
        if ($first === null) {
            return $next($request); // root `/`
        }

        $allowedLocales = cache()->remember('active_languages', 300, fn () => Language::where('is_active', 1)->pluck('code')->toArray());

        // Full legacy locale literal (it_IT, pt-PT, ...) -> short locale. Global,
        // so deep legacy URLs whose remaining segments match no group route still
        // get the same canonical hop SetLocale performs for matched routes.
        $short = SetLocale::legacyLocaleMap()[strtolower(str_replace('-', '_', $first))] ?? null;
        if ($short !== null && in_array($short, $allowedLocales, true)) {
            $rest = implode('/', array_slice($segments, 1));

            return $this->redirectWithQuery($request, '/'.$short.($rest !== '' ? '/'.$rest : ''));
        }

        if (! in_array($first, $allowedLocales, true)) {
            return $next($request); // unknown first segment: existing behaviour wins
        }

        $locale = $first;
        array_shift($segments); // drop the locale segment itself

        $first = $segments[0] ?? null;
        if ($first === null) {
            return $next($request); // `/{locale}` alone is the home route
        }

        $tail = array_slice($segments, 1);
        $family = $this->familyOf($first);

        if ($family === null) {
            return $next($request);
        }

        return match ($family) {
            'list' => $this->list($request, $next, $locale, $tail, $first === 'petitions'),
            'petition' => $this->petition($request, $next, $locale, $tail, $first === 'petition'),
            'user' => $this->user($request, $next, $locale, $tail, $first === 'user'),
            'search' => $this->search($request, $locale, $tail),
            'faq' => $this->redirectWithQuery($request, "/$locale/faqs"),
            'blog' => $this->redirectWithQuery($request, '/magazine/'),
            'topics', 'results' => $this->redirectWithQuery($request, "/$locale/petitions"),
            'recover' => $this->redirectWithQuery($request, "/$locale/forgot-password"),
            // `/download-signatures/{id}` needs an id→slug resolution the plain
            // redirect table can't provide: deferred, stays 404 (see report).
            'download' => abort(404),
            default => abort(404),
        };
    }

    private function familyOf(string $word): ?string
    {
        foreach (self::WORDS as $family => $byLocale) {
            if (in_array($word, $byLocale['canonical'] ?? [], true)) {
                return $family;
            }

            foreach ($byLocale as $locale => $words) {
                if ($locale !== 'canonical' && in_array($word, $words, true)) {
                    return $family;
                }
            }
        }

        return null;
    }

    private function list(Request $request, Closure $next, string $locale, array $tail, bool $canonical)
    {
        $page = $this->popPage($tail);

        $categorySegment = $tail[0] ?? null;
        $catWord = $this->categoryWordOf((string) $categorySegment);
        $category = $catWord !== null ? $this->parseCategory($categorySegment) : null;

        if ($category !== null) {
            // Canonical word form with the canonical category word is handled by
            // CategoryPetitionController (it canonicalises legacy ids in DB terms);
            // only rewrite when the legacy page segment needs translating.
            if ($canonical && $catWord === 'category') {
                if ($page === null) {
                    return $next($request);
                }

                return $this->redirectWithQuery($request, "/$locale/petitions/$categorySegment", $page);
            }

            $current = Category::find((($category['id'] - 1) % 22) + 1)
                ?? Category::whereHas('translations', fn ($q) => $q->where('slug', $category['slug']))->first();

            abort_unless($current, 404);

            $tr = $current->translation($locale);
            abort_unless($tr, 404);

            return $this->redirectWithQuery($request, "/$locale/petitions/category-{$tr->slug}-{$current->id}", $page);
        }

        if ($categorySegment !== null && preg_match('/^user(-\d+)?$/', $categorySegment)) {
            // legacy author-filtered list has no equivalent: point at the plain list
            return $this->redirectWithQuery($request, "/$locale/petitions");
        }

        if ($canonical && $page === null && $categorySegment === null) {
            return $next($request);
        }

        return $this->redirectWithQuery($request, "/$locale/petitions", $page);
    }

    private function petition(Request $request, Closure $next, string $locale, array $tail, bool $canonical)
    {
        $page = $this->popPage($tail);

        // Legacy hashed petition URLs: /{locale}/petition/{slug}/{id}/{hex}.
        // The trailing hex token is not a real route segment, so strip it with a
        // permanent redirect to the canonical petition URL.
        if ($page === null && count($tail) === 3 && ctype_digit($tail[1]) && preg_match('/^[0-9a-f]{16,}$/i', $tail[2])) {
            return $this->redirectWithQuery($request, "/$locale/petition/{$tail[0]}/{$tail[1]}");
        }

        $rest = implode('/', $tail);

        if ($canonical && $page === null) {
            return $next($request);
        }

        return $this->redirectWithQuery($request, "/$locale/petition".($rest !== '' ? "/$rest" : ''), $page);
    }

    private function user(Request $request, Closure $next, string $locale, array $tail, bool $canonical)
    {
        if ($canonical) {
            return $next($request);
        }

        $rest = implode('/', $tail);

        return $this->redirectWithQuery($request, "/$locale/user".($rest !== '' ? "/$rest" : ''));
    }

    private function search(Request $request, string $locale, array $tail)
    {
        $query = $tail[0] ?? null;
        $page = null;
        if ($query !== null && isset($tail[1])) {
            $page = ctype_digit($tail[1]) ? (int) $tail[1] : $this->parsePage($tail[1]);
        }

        abort_if($query === null, 404);

        return $this->redirectWithQuery($request, "/$locale/petitions", $page, ['q' => $query]);
    }

    /**
     * Remove and return a trailing `{pageWord}-N` segment, if present.
     */
    private function popPage(array &$tail): ?int
    {
        $last = $tail[count($tail) - 1] ?? null;
        $page = $last !== null ? $this->parsePage($last) : null;

        if ($page !== null) {
            array_pop($tail);
        }

        return $page;
    }

    private function parsePage(string $segment): ?int
    {
        foreach (self::PAGE as $word) {
            if (preg_match('/^'.preg_quote($word, '/').'-(\d+)$/', $segment, $m)) {
                return (int) $m[1];
            }
        }

        return null;
    }

    private function categoryWordOf(string $segment): ?string
    {
        foreach (self::CATEGORY as $word) {
            if (str_starts_with($segment, $word.'-')) {
                return $word;
            }
        }

        return null;
    }

    private function parseCategory(string $segment): ?array
    {
        $word = $this->categoryWordOf($segment);

        if ($word === null || ! preg_match('/^'.preg_quote($word, '/').'-(.+)-(\d+)$/', $segment, $m)) {
            return null;
        }

        return ['slug' => $m[1], 'id' => (int) $m[2]];
    }

    private function redirectWithQuery(Request $request, string $target, ?int $page = null, array $extra = []): RedirectResponse
    {
        $query = [];
        parse_str((string) $request->getQueryString(), $query);

        foreach ($extra as $key => $value) {
            $query[$key] = $value;
        }

        if ($page !== null) {
            $query['page'] = (string) $page;
        }

        return redirect($target.($query !== [] ? '?'.http_build_query($query) : ''), 301);
    }
}
