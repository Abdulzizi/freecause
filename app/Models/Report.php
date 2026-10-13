<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $fillable = [
        'reportable_type',
        'reportable_id',
        'reason',
        'details',
        'reporter_id',
        'ip',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function reasons(): array
    {
        return [
            'spam',
            'abuse',
            'misinformation',
            'copyright',
            'other',
        ];
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}
