<?php

namespace App\Rules;

use App\Models\Petition;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YoutubeVideo implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || Petition::youtubeId($value) === null) {
            $fail('validation.regex')->translate();
        }
    }
}
