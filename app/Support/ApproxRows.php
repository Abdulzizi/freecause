<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ApproxRows
{
    protected function approxTableRows(string $table): int
    {
        return (int) Cache::remember("approx_rows:$table", 3600, function () use ($table) {
            $db = DB::getDatabaseName();

            $row = DB::table('information_schema.TABLES')
                ->select('TABLE_ROWS')
                ->where('TABLE_SCHEMA', $db)
                ->where('TABLE_NAME', $table)
                ->first();

            return $row ? (int) $row->TABLE_ROWS : 0;
        });
    }
}
