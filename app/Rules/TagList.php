<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;

class TagList implements ValidationRule
{
    public function __construct(
        private int $max = 10,
        private int $maxLength = 30,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Collection<int, string> $tags */
        $tags = collect(explode(',', (string) $value))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values();

        if ($tags->count() > $this->max) {
            $fail("Tags: maximum {$this->max} keywords.");
        }

        if ($tags->contains(fn ($tag) => mb_strlen($tag) > $this->maxLength)) {
            $fail("Tags: each keyword must be {$this->maxLength} characters or less.");
        }
    }
}
