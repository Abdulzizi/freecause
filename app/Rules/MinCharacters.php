<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinCharacters implements ValidationRule
{
    public function __construct(
        private int $min = 30,
        private string $label = 'Text',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));

        if (mb_strlen($text) < $this->min) {
            $fail("{$this->label} must contain at least {$this->min} characters of meaningful content.");
        }
    }
}
