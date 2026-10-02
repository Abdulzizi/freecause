<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for the copy-pasted "translation fallback" JOIN.
 *
 * The legacy pattern LEFT JOINs a translation table twice (once for the current
 * locale, once for the default locale) and then SELECTs COALESCE(locale.col,
 * default.col) AS alias for every translated column. Aliases and columns differ
 * per query, so everything is parameterized via a $config array.
 *
 * Callers MUST select their base columns explicitly before applying the joins,
 * e.g. `Petition::select(['petitions.id'])->withTranslation([...])`. The helper
 * only appends the two LEFT JOINs and the COALESCE select expressions.
 */
class TranslationJoin
{
    /**
     * Apply the locale + default translation joins and COALESCE selects to a
     * query builder (Eloquent or base) and return it for chaining.
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     * @param  array{
     *     base: string,
     *     base_key: string,
     *     translation_table: string,
     *     translation_key: string,
     *     locale_alias: string,
     *     default_alias: string,
     *     locale: string,
     *     default_locale: string,
     *     columns: array<string, string>,
     * }  $config
     * @return EloquentBuilder|QueryBuilder
     */
    public static function apply($query, array $config)
    {
        // For Eloquent builders, mutate the inner base query so the Eloquent
        // builder (and its model hydration) is preserved and returned for chaining.
        $target = $query instanceof EloquentBuilder ? $query->getQuery() : $query;

        $target->leftJoin(
            "{$config['translation_table']} as {$config['locale_alias']}",
            function ($join) use ($config) {
                $join->on(
                    "{$config['locale_alias']}.{$config['translation_key']}",
                    '=',
                    "{$config['base']}.{$config['base_key']}"
                )->where("{$config['locale_alias']}.locale", '=', $config['locale']);
            }
        );

        $target->leftJoin(
            "{$config['translation_table']} as {$config['default_alias']}",
            function ($join) use ($config) {
                $join->on(
                    "{$config['default_alias']}.{$config['translation_key']}",
                    '=',
                    "{$config['base']}.{$config['base_key']}"
                )->where("{$config['default_alias']}.locale", '=', $config['default_locale']);
            }
        );

        foreach ($config['columns'] as $source => $alias) {
            $target->selectRaw(
                "COALESCE({$config['locale_alias']}.{$source}, {$config['default_alias']}.{$source}) as {$alias}"
            );
        }

        return $query;
    }

    /**
     * Build the raw-SQL fragments (two LEFT JOIN clauses, COALESCE selects and
     * the ordered bindings) for hand-written DB::select() queries.
     *
     * @param  array{
     *     base: string,
     *     base_key: string,
     *     translation_table: string,
     *     translation_key: string,
     *     locale_alias: string,
     *     default_alias: string,
     *     locale: string,
     *     default_locale: string,
     *     columns: array<string, string>,
     * }  $config
     * @return array{locale_join: string, default_join: string, selects: array<int, string>, bindings: array<int, string>}
     */
    public static function raw(array $config): array
    {
        $localeJoin = sprintf(
            'LEFT JOIN %s %s ON %s.%s = %s.%s AND %s.locale = ?',
            $config['translation_table'],
            $config['locale_alias'],
            $config['locale_alias'],
            $config['translation_key'],
            $config['base'],
            $config['base_key'],
            $config['locale_alias']
        );

        $defaultJoin = sprintf(
            'LEFT JOIN %s %s ON %s.%s = %s.%s AND %s.locale = ?',
            $config['translation_table'],
            $config['default_alias'],
            $config['default_alias'],
            $config['translation_key'],
            $config['base'],
            $config['base_key'],
            $config['default_alias']
        );

        $selects = [];
        foreach ($config['columns'] as $source => $alias) {
            $selects[] = sprintf(
                'COALESCE(%s.%s, %s.%s) as %s',
                $config['locale_alias'],
                $source,
                $config['default_alias'],
                $source,
                $alias
            );
        }

        return [
            'locale_join' => $localeJoin,
            'default_join' => $defaultJoin,
            'selects' => $selects,
            'bindings' => [$config['locale'], $config['default_locale']],
        ];
    }
}
