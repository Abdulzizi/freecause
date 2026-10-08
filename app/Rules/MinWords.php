<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinWords implements ValidationRule
{
    public function __construct(
        private int $min = 3,
        private string $label = 'Title',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $words = array_values(array_filter(
            preg_split('/\s+/', trim((string) $value)),
            fn ($word) => $word !== '',
        ));

        if (count($words) < $this->min) {
            $fail("{$this->label} must contain at least {$this->min} words.");
        }
    }
}
