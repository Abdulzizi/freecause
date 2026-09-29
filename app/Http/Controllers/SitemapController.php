<?php

namespace App\Http\Controllers;

use App\Models\PageTranslation;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    private const CACHE_TTL = 3600;

    private const PETITIONS_PER_FILE = 50000;

    private const STATIC_PAGE_SLUGS = [
        'faqs',
        'how-to-write-a-petition',
        'donations',
        'personal-data-processing',
        'contacts',
    ];

    public function index()
    {
        return $this->xml($this->cached(__FUNCTION__, function () {
            return '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".
                $this->indexEntry('sitemap-pages.xml').
                $this->indexEntry('sitemap-categories.xml').
                $this->petitionIndexEntries().
                $this->indexEntry('sitemap-magazine.xml').
                '</sitemapindex>';
        }));
    }

    private function petitionIndexEntries(): string
    {
        $total = $this->petitionRowCount();

        $out = '';
        foreach (range(1, max(1, (int) ceil($total / self::PETITIONS_PER_FILE))) as $page) {
            $out .= $this->indexEntry('sitemap-petitions-'.$page.'.xml');
        }

        return $out;
    }

    private function petitionRowCount(): int
    {
        return $this->cachedInt('petitions.count', function () {
            return DB::table('petitions as p')
                ->join('petition_translations as pt', 'pt.petition_id', '=', 'p.id')
                ->where('p.status', 'published')
                ->where('p.is_active', 1)
                ->whereIn('pt.locale', active_locales())
                ->count();
        });
    }

    public function pages()
    {
        return $this->xml($this->cached(__FUNCTION__, function () {
            $pagesByLocale = [];

            foreach (active_locales() as $locale) {
                $pagesByLocale[$locale] = [$this->pageEntry(canonical_url($locale))];
                $pagesByLocale[$locale][] = $this->pageEntry(canonical_url($locale.'/petitions'));

                foreach (self::STATIC_PAGE_SLUGS as $slug) {
                    $pagesByLocale[$locale][] = $this->pageEntry(canonical_url($locale.'/'.$slug));
                }

                // Auth pages are locale-prefixed routes but not public static pages.
                $pagesByLocale[$locale][] = $this->pageEntry(lroute('register', ['locale' => $locale]));
            }

            $entries = $this->withLocaleAlternates($pagesByLocale);

            foreach (PageTranslation::query()
                ->where('published', true)
                ->whereIn('locale', active_locales())
                ->orderBy('locale')
                ->get(['locale', 'slug', 'updated_at']) as $tr) {
                $entries[] = $this->pageEntry(
                    canonical_url($tr->locale.'/'.$tr->slug),
                    $tr->updated_at?->toDateString()
                );
            }

            $entries[] = $this->pageEntry(canonical_url('magazine'));

            return $this->urlset($entries);
        }));
    }

    public function categories()
    {
        return $this->xml($this->cached(__FUNCTION__, function () {
            $rows = DB::table('categories as c')
                ->join('category_translations as ct', 'ct.category_id', '=', 'c.id')
                ->where('c.is_active', 1)
                ->whereIn('ct.locale', active_locales())
                ->select('c.id', 'ct.locale', 'ct.slug', 'c.updated_at', 'ct.updated_at as tr_updated_at')
                ->get();

            $entries = [];
            foreach ($rows->groupBy('id') as $id => $variants) {
                $alternates = [];
                foreach ($variants as $v) {
                    $alternates[] = [
                        'hreflang' => $v->locale,
                        'href' => canonical_url($v->locale.'/petitions/category-'.$v->slug.'-'.$id),
                    ];
                }

                $dates = $variants->pluck('updated_at')->filter()->map(fn ($d) => (string) $d)->all();
                $lastmod = $dates ? max($dates) : null;
                foreach ($variants as $v) {
                    $entries[] = $this->pageEntry(
                        canonical_url($v->locale.'/petitions/category-'.$v->slug.'-'.$id),
                        $lastmod ? $this->dateOnly($lastmod) : null,
                        $alternates
                    );
                }
            }

            return $this->urlset($entries);
        }));
    }

    public function petitions(int $page = 1)
    {
        return $this->xml($this->cached(__FUNCTION__.'.'.$page, function () use ($page) {
            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

            $rows = DB::table('petitions as p')
                ->join('petition_translations as pt', 'pt.petition_id', '=', 'p.id')
                ->where('p.status', 'published')
                ->where('p.is_active', 1)
                ->whereIn('pt.locale', active_locales())
                ->select('p.id', 'p.updated_at as p_updated_at', 'pt.locale', 'pt.slug', 'pt.updated_at')
                ->orderBy('p.id')
                ->offset(($page - 1) * self::PETITIONS_PER_FILE)
                ->limit(self::PETITIONS_PER_FILE)
                ->cursor();

            $group = [];
            $currentId = null;

            foreach ($rows as $row) {
                if ($currentId !== null && $row->id !== $currentId) {
                    $out .= $this->petitionGroupXml($currentId, $group);
                    $group = [];
                }
                $currentId = $row->id;
                $group[] = $row;
            }

            if ($currentId !== null) {
                $out .= $this->petitionGroupXml($currentId, $group);
            }

            return $out.'</urlset>';
        }));
    }

    public function magazine()
    {
        return $this->xml($this->cached(__FUNCTION__, function () {
            try {
                $posts = DB::connection('magazine')->select("
                    SELECT ID AS id, post_name, post_modified
                    FROM abi_posts
                    WHERE post_status = 'publish' AND post_type = 'post'
                    ORDER BY post_modified DESC
                ");
            } catch (\Throwable $e) {
                Log::error('Sitemap: magazine DB connection failed - '.$e->getMessage());

                $posts = [];
            }

            $entries = [];
            foreach ($posts as $post) {
                $post = is_array($post) ? (object) $post : $post;

                $entries[] = $this->pageEntry(
                    canonical_url('magazine/'.$post->post_name),
                    ! empty($post->post_modified) ? $this->dateOnly((string) $post->post_modified) : null
                );
            }

            return $this->urlset($entries);
        }));
    }

    private function withLocaleAlternates(array $pagesByLocale): array
    {
        $entries = [];

        foreach (active_locales() as $locale) {
            $current = $pagesByLocale[$locale];

            foreach ($current as $i => $entry) {
                $alternates = [];
                foreach (active_locales() as $otherLocale) {
                    $alternates[] = [
                        'hreflang' => $otherLocale,
                        'href' => $pagesByLocale[$otherLocale][$i]['loc'],
                    ];
                }

                if ($locale === default_locale()) {
                    $alternates[] = ['hreflang' => 'x-default', 'href' => $entry['loc']];
                }

                $entry['alternates'] = $alternates;
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    private function petitionGroupXml(int $id, array $variants): string
    {
        $alternates = [];
        $lastmods = [];

        foreach ($variants as $v) {
            $alternates[] = [
                'hreflang' => $v->locale,
                'href' => canonical_url($v->locale.'/petition/'.$v->slug.'/'.$id),
            ];

            foreach ([$v->p_updated_at, $v->updated_at] as $date) {
                if ($date) {
                    $lastmods[] = (string) $date;
                }
            }
        }

        $lastmod = $lastmods ? $this->dateOnly(max($lastmods)) : null;

        $out = '';
        foreach ($variants as $v) {
            $out .= "\t<url>\n";
            $out .= "\t\t<loc>".$this->xmlEscape(canonical_url($v->locale.'/petition/'.$v->slug.'/'.$id))."</loc>\n";

            if ($lastmod !== null) {
                $out .= "\t\t<lastmod>".$this->xmlEscape($lastmod)."</lastmod>\n";
            }

            foreach ($alternates as $alt) {
                $out .= "\t\t<xhtml:link rel=\"alternate\" hreflang=\"".$this->xmlEscape($alt['hreflang']).'" href="'.$this->xmlEscape($alt['href'])."\"/>\n";
            }

            $out .= "\t</url>\n";
        }

        return $out;
    }

    private function pageEntry(string $loc, ?string $lastmod = null, array $alternates = []): array
    {
        $entry = ['loc' => $loc];
        if ($lastmod !== null) {
            $entry['lastmod'] = $lastmod;
        }
        if ($alternates !== []) {
            $entry['alternates'] = $alternates;
        }

        return $entry;
    }

    private function indexEntry(string $filename): string
    {
        return "\t<sitemap>\n".
            "\t\t<loc>".$this->xmlEscape(canonical_url($filename))."</loc>\n".
            "\t</sitemap>\n";
    }

    private function urlset(array $entries): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($entries as $entry) {
            $out .= "\t<url>\n";
            $out .= "\t\t<loc>".$this->xmlEscape($entry['loc'])."</loc>\n";

            if (isset($entry['lastmod'])) {
                $out .= "\t\t<lastmod>".$this->xmlEscape($entry['lastmod'])."</lastmod>\n";
            }

            foreach ($entry['alternates'] ?? [] as $alt) {
                $out .= "\t\t<xhtml:link rel=\"alternate\" hreflang=\"".$this->xmlEscape($alt['hreflang']).'" href="'.$this->xmlEscape($alt['href'])."\"/>\n";
            }

            $out .= "\t</url>\n";
        }

        return $out.'</urlset>';
    }

    private function cached(string $key, Closure $callback): string
    {
        return Cache::remember('sitemap:'.$key, self::CACHE_TTL, $callback);
    }

    private function cachedInt(string $key, Closure $callback): int
    {
        return (int) Cache::remember('sitemap:'.$key, self::CACHE_TTL, $callback);
    }

    private function dateOnly(string $value): string
    {
        return substr($value, 0, 10);
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function xml(string $content): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
