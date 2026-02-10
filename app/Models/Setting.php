<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    public $timestamps = true;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    public function castedValue($default = null)
    {
        if ($this->value === null) {
            return $default;
        }

        switch ($this->type) {
            case 'bool':
            case 'boolean':
                return in_array(
                    strtolower((string) $this->value),
                    ['1', 'true', 'yes', 'on'],
                    true
                );

            case 'int':
            case 'integer':
                return (int) $this->value;

            case 'float':
            case 'double':
                return (float) $this->value;

            case 'json':
            case 'array':
                $decoded = json_decode((string) $this->value, true);
                return $decoded === null ? $default : $decoded;

            case 'text':
            case 'string':
            default:
                return (string) $this->value;
        }
    }
}
