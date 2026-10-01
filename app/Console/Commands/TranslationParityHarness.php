<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Petition;
use App\Support\TranslationJoin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Translation-fallback refactor parity harness.
 *
 * Runs a representative sample of the collapsed translation-fallback JOINs in
 * both the legacy (copy-pasted) form and the shared-helper form, dumps the rows
 * and lets the caller diff the two. The result rows must be byte-identical.
 *
 * Usage:
 *   php artisan translation:parity --mode=old --out=/tmp/parity-before.json
 *   php artisan translation:parity --mode=new --out=/tmp/parity-after.json
 */
class TranslationParityHarness extends Command
{
    protected $signature = 'translation:parity {--mode=old : old|new} {--out= : output file path}';

    protected $description = 'Dump translation-fallback query rows (old vs new) for parity diffing';

    public function handle(): int
    {
        $mode = $this->option('mode');
        $useHelper = $mode === 'new';

        if (! in_array($mode, ['old', 'new'], true)) {
            $this->error('--mode must be "old" or "new".');

            return 1;
        }

        $default = default_locale();
        $locales = ['en', 'nl'];

        $featuredId = Petition::where('status', 'published')->where('is_active', 1)->value('id');

        $out = [];

        foreach ($locales as $locale) {
            $out['category_tr'][$locale] = $this->categoryTr($locale, $default, $useHelper);
            $out['category_name_slug'][$locale] = $this->categoryNameSlug($locale, $default, $useHelper);
            $out['category_name'][$locale] = $this->categoryName($locale, $default, $useHelper);
            $out['petition_tr'][$locale] = $this->petitionTr($locale, $default, $useHelper);
            $out['petition_title_slug_p'][$locale] = $this->petitionTitleSlugP($locale, $default, $useHelper);
            $out['petition_featured'][$locale] = $this->petitionFeatured($locale, $default, $useHelper, $featuredId);
            $out['petition_raw'][$locale] = $this->petitionRaw($locale, $default, $useHelper);
            $out['petition_pool'][$locale] = $this->petitionPool($locale, $default, $useHelper);
        }

        $json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $target = $this->option('out') ?: '/tmp/parity-'.($useHelper ? 'after' : 'before').'.json';
        file_put_contents($target, $json.PHP_EOL);

        $this->info("Wrote {$target} ({$mode} mode, ".strlen($json).' bytes).');

        return 0;
    }

    private function categoryTr(string $locale, string $default, bool $useHelper)
    {
        $query = Category::query()->select(['categories.id', 'categories.sort_order']);

        $config = [
            'base' => 'categories',
            'base_key' => 'id',
            'translation_table' => 'category_translations',
            'translation_key' => 'category_id',
            'locale_alias' => 'ct_locale',
            'default_alias' => 'ct_default',
            'locale' => $locale,
            'default_locale' => $default,
            'columns' => ['name' => 'tr_name', 'slug' => 'tr_slug'],
        ];

        if ($useHelper) {
            $query->withTranslation($config);
        } else {
            $query->select([
                'categories.id',
                'categories.sort_order',
                DB::raw('COALESCE(ct_locale.name, ct_default.name) as tr_name'),
                DB::raw('COALESCE(ct_locale.slug, ct_default.slug) as tr_slug'),
            ])
                ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                    $join->on('ct_locale.category_id', '=', 'categories.id')
                        ->where('ct_locale.locale', '=', $locale);
                })
                ->leftJoin('category_translations as ct_default', function ($join) use ($default) {
                    $join->on('ct_default.category_id', '=', 'categories.id')
                        ->where('ct_default.locale', '=', $default);
                });
        }

        return $this->norm($query->where('categories.is_active', true)->orderBy('categories.sort_order')->get());
    }

    private function categoryNameSlug(string $locale, string $default, bool $useHelper)
    {
        $config = [
            'base' => 'categories',
            'base_key' => 'id',
            'translation_table' => 'category_translations',
            'translation_key' => 'category_id',
            'locale_alias' => 'ct_locale',
            'default_alias' => 'ct_default',
            'locale' => $locale,
            'default_locale' => $default,
            'columns' => ['name' => 'name', 'slug' => 'slug'],
        ];

        $query = Category::query()->select(['categories.id']);

        if ($useHelper) {
            $query->withTranslation($config);
        } else {
            $query->selectRaw('COALESCE(ct_locale.name, ct_default.name) as name')
                ->selectRaw('COALESCE(ct_locale.slug, ct_default.slug) as slug')
                ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                    $join->on('ct_locale.category_id', '=', 'categories.id')
                        ->where('ct_locale.locale', '=', $locale);
                })
                ->leftJoin('category_translations as ct_default', function ($join) use ($default) {
                    $join->on('ct_default.category_id', '=', 'categories.id')
                        ->where('ct_default.locale', '=', $default);
                });
        }

        return $this->norm($query->where('categories.is_active', true)->orderBy('name')->get());
    }

    private function categoryName(string $locale, string $default, bool $useHelper)
    {
        $config = [
            'base' => 'categories',
            'base_key' => 'id',
            'translation_table' => 'category_translations',
            'translation_key' => 'category_id',
            'locale_alias' => 'ct_locale',
            'default_alias' => 'ct_default',
            'locale' => $locale,
            'default_locale' => $default,
            'columns' => ['name' => 'name'],
        ];

        $query = Category::query()->select(['categories.id']);

        if ($useHelper) {
            $query->withTranslation($config);
        } else {
            $query->selectRaw('COALESCE(ct_locale.name, ct_default.name) as name')
                ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                    $join->on('ct_locale.category_id', '=', 'categories.id')
                        ->where('ct_locale.locale', '=', $locale);
                })
                ->leftJoin('category_translations as ct_default', function ($join) use ($default) {
                    $join->on('ct_default.category_id', '=', 'categories.id')
                        ->where('ct_default.locale', '=', $default);
                });
        }

        return $this->norm($query->where('categories.is_active', true)->orderBy('name')->get());
    }

    private function petitionTr(string $locale, string $default, bool $useHelper)
    {
        $config = [
            'base' => 'petitions',
            'base_key' => 'id',
            'translation_table' => 'petition_translations',
            'translation_key' => 'petition_id',
            'locale_alias' => 'pt_locale',
            'default_alias' => 'pt_default',
            'locale' => $locale,
            'default_locale' => $default,
            'columns' => ['title' => 'tr_title', 'slug' => 'tr_slug'],
        ];

        $query = Petition::query()->select([
            'petitions.id',
            'petitions.signature_count',
            'petitions.goal_signatures',
            'petitions.category_id',
            'petitions.cover_image',
        ]);

        if ($useHelper) {
            $query->withTranslation($config);
        } else {
            $query->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.goal_signatures',
                'petitions.category_id',
                'petitions.cover_image',
                DB::raw('COALESCE(pt_locale.title, pt_default.title) as tr_title'),
                DB::raw('COALESCE(pt_locale.slug, pt_default.slug) as tr_slug'),
            ])
                ->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                    $join->on('pt_locale.petition_id', '=', 'petitions.id')
                        ->where('pt_locale.locale', '=', $locale);
                })
                ->leftJoin('petition_translations as pt_default', function ($join) use ($default) {
                    $join->on('pt_default.petition_id', '=', 'petitions.id')
                        ->where('pt_default.locale', '=', $default);
                });
        }

        return $this->norm($query->where(function ($q) {
            $q->whereNotNull('pt_locale.title')->orWhereNotNull('pt_default.title');
        })->where('petitions.status', 'published')->where('petitions.is_active', 1)->orderByDesc('petitions.id')->limit(15)->get());
    }

    private function petitionTitleSlugP(string $locale, string $default, bool $useHelper)
    {
        $config = [
            'base' => 'p',
            'base_key' => 'id',
            'translation_table' => 'petition_translations',
            'translation_key' => 'petition_id',
            'locale_alias' => 'pt_locale',
            'default_alias' => 'pt_default',
            'locale' => $locale,
            'default_locale' => $default,
            'columns' => ['title' => 'title', 'slug' => 'slug'],
        ];

        $query = DB::table('petitions as p');

        if ($useHelper) {
            $query->select(['p.id', 'p.signature_count', 'p.created_at'])->withTranslation($config);
        } else {
            $query->leftJoin('petition_translations as pt_locale', function ($j) use ($locale) {
                $j->on('pt_locale.petition_id', '=', 'p.id')
                    ->where('pt_locale.locale', '=', $locale);
            })
                ->leftJoin('petition_translations as pt_default', function ($j) use ($default) {
                    $j->on('pt_default.petition_id', '=', 'p.id')
                        ->where('pt_default.locale', '=', $default);
                })
                ->select([
                    'p.id',
                    DB::raw('COALESCE(pt_locale.title, pt_default.title) as title'),
                    DB::raw('COALESCE(pt_locale.slug, pt_default.slug) as slug'),
                    'p.signature_count',
                    'p.created_at',
                ]);
        }

        return $this->norm($query->where('p.status', 'published')
            ->whereRaw('(pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)')
            ->orderByDesc('p.id')
            ->limit(5)
            ->get());
    }

    private function petitionFeatured(string $locale, string $default, bool $useHelper, ?int $featuredId)
    {
        if ($featuredId === null) {
            return null;
        }

        $config = [
            'base' => 'petitions',
            'base_key' => 'id',
            'translation_table' => 'petition_translations',
            'translation_key' => 'petition_id',
            'locale_alias' => 'pt_locale',
            'default_alias' => 'pt_default',
            'locale' => $locale,
            'default_locale' => $default,
            'columns' => ['title' => 'tr_title', 'slug' => 'tr_slug', 'description' => 'tr_description'],
        ];

        $query = Petition::query()->select(['petitions.*']);

        if ($useHelper) {
            $query->withTranslation($config);
        } else {
            $query->select([
                'petitions.*',
                DB::raw('COALESCE(pt_locale.title, pt_default.title) as tr_title'),
                DB::raw('COALESCE(pt_locale.slug, pt_default.slug) as tr_slug'),
                DB::raw('COALESCE(pt_locale.description, pt_default.description) as tr_description'),
            ])
                ->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                    $join->on('pt_locale.petition_id', '=', 'petitions.id')
                        ->where('pt_locale.locale', '=', $locale);
                })
                ->leftJoin('petition_translations as pt_default', function ($join) use ($default) {
                    $join->on('pt_default.petition_id', '=', 'petitions.id')
                        ->where('pt_default.locale', '=', $default);
                });
        }

        return $this->norm($query->where('petitions.id', $featuredId)->first());
    }

    private function petitionRaw(string $locale, string $default, bool $useHelper)
    {
        if ($useHelper) {
            $tr = TranslationJoin::raw([
                'base' => 'p',
                'base_key' => 'id',
                'translation_table' => 'petition_translations',
                'translation_key' => 'petition_id',
                'locale_alias' => 'pt_locale',
                'default_alias' => 'pt_default',
                'locale' => $locale,
                'default_locale' => $default,
                'columns' => ['title' => 'petition_title', 'slug' => 'petition_slug'],
            ]);

            $rows = DB::select("
                SELECT s.*, p.id as petition_id,
                    {$tr['selects'][0]},
                    {$tr['selects'][1]}
                FROM signatures s
                JOIN petitions p ON p.id = s.petition_id AND p.status = 'published' AND p.is_active = 1
                {$tr['locale_join']}
                {$tr['default_join']}
                WHERE s.confirmed = 1 AND s.is_spam = 0
                    AND (pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)
                ORDER BY s.created_at DESC
                LIMIT 10
            ", $tr['bindings']);
        } else {
            $rows = DB::select("
                SELECT s.*, p.id as petition_id,
                    COALESCE(pt_locale.title, pt_default.title) as petition_title,
                    COALESCE(pt_locale.slug, pt_default.slug) as petition_slug
                FROM signatures s
                JOIN petitions p ON p.id = s.petition_id AND p.status = 'published' AND p.is_active = 1
                LEFT JOIN petition_translations pt_locale ON pt_locale.petition_id = p.id AND pt_locale.locale = ?
                LEFT JOIN petition_translations pt_default ON pt_default.petition_id = p.id AND pt_default.locale = ?
                WHERE s.confirmed = 1 AND s.is_spam = 0
                    AND (pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)
                ORDER BY s.created_at DESC
                LIMIT 10
            ", [$locale, $default]);
        }

        return $this->norm($rows);
    }

    private function petitionPool(string $locale, string $default, bool $useHelper)
    {
        $config = [
            'base' => 'petitions',
            'base_key' => 'id',
            'translation_table' => 'petition_translations',
            'translation_key' => 'petition_id',
            'locale_alias' => 'pt_locale',
            'default_alias' => 'pt_default',
            'locale' => $locale,
            'default_locale' => $default,
            'columns' => [],
        ];

        $query = Petition::query()->select(['petitions.id']);

        if ($useHelper) {
            $query->withTranslation($config);
        } else {
            $query->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                $join->on('pt_locale.petition_id', '=', 'petitions.id')
                    ->where('pt_locale.locale', '=', $locale);
            })
                ->leftJoin('petition_translations as pt_default', function ($join) use ($default) {
                    $join->on('pt_default.petition_id', '=', 'petitions.id')
                        ->where('pt_default.locale', '=', $default);
                });
        }

        return $this->norm($query->whereNotNull('pt_locale.title')
            ->where('pt_locale.is_featured', 1)
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1)
            ->orderByDesc('petitions.signature_count')
            ->limit(5)
            ->pluck('petitions.id')
            ->toArray());
    }

    private function norm($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->norm($v);
            }
            ksort($out);

            return $out;
        }

        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return $this->norm($value->toArray());
            }

            return $this->norm((array) $value);
        }

        return $value;
    }
}
