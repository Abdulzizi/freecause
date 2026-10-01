<?php

namespace App\Support;

class CategoryIcons
{
    /** @var array<int, string> */
    protected static array $map = [
        1 => 'bi-hearts',
        2 => 'bi-briefcase',
        3 => 'bi-building',
        4 => 'bi-palette',
        5 => 'bi-book',
        6 => 'bi-tree',
        7 => 'bi-heart-pulse',
        8 => 'bi-person-check',
        9 => 'bi-flag',
        10 => 'bi-cpu',
        11 => 'bi-trophy',
        12 => 'bi-airplane',
        13 => 'bi-car-front',
        14 => 'bi-people',
        15 => 'bi-house-heart',
    ];

    public static function get(int $categoryId, string $default = 'bi-collection'): string
    {
        return self::$map[$categoryId] ?? $default;
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return self::$map;
    }

    public static function has(int $categoryId): bool
    {
        return array_key_exists($categoryId, self::$map);
    }
}
