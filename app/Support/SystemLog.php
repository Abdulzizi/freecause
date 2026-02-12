<?php

namespace App\Support;

use App\Models\Log;

class SystemLog
{
    public static function write(
        string $level,
        string $context,
        string $title,
        ?string $content = null
    ): void {
        Log::create([
            'level'   => $level,
            'context' => $context,
            'title'   => $title,
            'content' => $content,
            'ip'      => request()->ip(),
        ]);
    }
}
