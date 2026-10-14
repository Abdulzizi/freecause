<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ApproxRows
{
    protected function approxTableRows(string $table): int
    {
        $allowed = [
            'users',
            'petitions',
            'signatures',
            'categories',
            'pages',
            'logs',
            'banned_ips',
            'reports',
        ];

        if (! in_array($table, $allowed, true)) {
            return 0;
        }

        return (int) Cache::remember("approx_rows:$table", 3600, function () use ($table) {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $db = DB::getDatabaseName();

                $row = DB::table('information_schema.TABLES')
                    ->select('TABLE_ROWS')
                    ->where('TABLE_SCHEMA', $db)
                    ->where('TABLE_NAME', $table)
                    ->first();

                return $row ? (int) $row->TABLE_ROWS : 0;
            }

            return DB::table($table)->count();
        });
    }
}
