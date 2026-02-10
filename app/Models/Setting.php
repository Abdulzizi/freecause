<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public function castedValue()
    {
        return match ($this->type) {
            'int' => (int) $this->value,
            'bool' => (bool) (int) $this->value,
            'json' => $this->value ? json_decode($this->value, true) : null,
            default => $this->value,
        };
    }
}
