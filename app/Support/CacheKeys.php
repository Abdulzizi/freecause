<?php

namespace App\Support;

class CacheKeys
{
    public static function homeCategories(string $locale): string
    {
        return 'home:categories:'.CacheVersion::get('home').':'.$locale;
    }

    public static function homeRecent(string $locale): string
    {
        return 'home:recent:'.CacheVersion::get('home').':'.$locale;
    }

    public static function homePool(string $locale, array $excludedIds = [], int $maxFeatured = 0): string
    {
        return 'home:pool:'.CacheVersion::get('home').':'.$locale.':'.md5(implode(',', $excludedIds).':'.$maxFeatured);
    }

    public static function homeFeatured(string $locale, int $featuredId): string
    {
        return 'home:featured:'.CacheVersion::get('home').':'.$locale.':'.$featuredId;
    }

    public static function homeMagazinePosts(): string
    {
        return 'home:magazine_posts:'.CacheVersion::get('home');
    }

    public static function petitionsIndex(string $locale, int $page): string
    {
        return 'petitions:index:v2:'.$locale.':page:'.$page.':'.CacheVersion::get('list');
    }

    public static function petitionsTrending(string $locale, int $page): string
    {
        return 'petitions:trending:v2:'.$locale.':page:'.$page.':'.CacheVersion::get('list');
    }

    public static function petitionsByCategory(string $locale, int $categoryId, int $page): string
    {
        return 'petitions:byCategory:v2:'.$locale.':'.$categoryId.':page:'.$page.':'.CacheVersion::get('list');
    }
}
