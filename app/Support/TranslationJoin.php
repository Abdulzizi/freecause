<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class TranslationJoin
{
    public static function apply($query, array $config)
    {

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
