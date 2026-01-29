<?php

if (! function_exists('locale_url')) {
    function locale_url(string $locale): string
    {
        $segments = request()->segments();

        // replace first segment (locale)
        if (isset($segments[0])) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        return '/' . implode('/', $segments);
    }
}
