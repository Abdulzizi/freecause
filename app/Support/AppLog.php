<?php

namespace App\Support;

use App\Models\Log;
use Illuminate\Support\Facades\Request;

class AppLog
{
    public static function info(string $title, string $content = '', ?string $context = null): void
    {
        self::store('info', $title, $content, $context);
    }

    public static function warning(string $title, string $content = '', ?string $context = null): void
    {
        self::store('warning', $title, $content, $context);
    }

    public static function error(string $title, string $content = '', ?string $context = null): void
    {
        self::store('error', $title, $content, $context);
    }

    protected static function store(string $level, string $title, string $content = '', ?string $context = null): void
    {
        Log::create([
            'level'   => $level,
            'title'   => $title,
            'content' => $content,
            'context' => $context,
            'ip'      => request()->ip(),
        ]);
    }
}
