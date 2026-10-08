<?php

namespace Tests\Unit;

use App\Support\Slug;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    public function test_returns_base_slug_when_there_is_no_collision(): void
    {
        $this->assertSame('my-petition-title', Slug::dedupe('My Petition Title', fn () => false));
    }

    public function test_appends_incrementing_suffix_until_a_slug_is_free(): void
    {
        $taken = ['my-petition-title', 'my-petition-title-1'];

        $result = Slug::dedupe('My Petition Title', fn (string $slug) => in_array($slug, $taken, true));

        $this->assertSame('my-petition-title-2', $result);
    }

    public function test_empty_value_falls_back_to_the_provided_fallback(): void
    {
        $never = fn () => false;

        $this->assertSame('my-cause', Slug::dedupe('', $never, 'My Cause'));
        $this->assertSame('item', Slug::dedupe('', $never));
        $this->assertSame('item', Slug::dedupe('!!! ???', $never, '###'));
    }

    public function test_max_length_truncates_the_base_slug(): void
    {
        $slug = Slug::dedupe('a very long slug value that exceeds ten chars', fn () => false, 'item', 10);

        $this->assertSame('a-very-lon', $slug);
        $this->assertLessThanOrEqual(10, mb_strlen($slug));
    }

    public function test_max_length_holds_even_when_a_suffix_is_appended(): void
    {
        $taken = ['abcdefghij', 'abcdefgh-1', 'abcdefgh-2'];

        $slug = Slug::dedupe('abcdefghijklmnop', fn (string $s) => in_array($s, $taken, true), 'item', 10);

        $this->assertSame('abcdefgh-3', $slug);
        $this->assertLessThanOrEqual(10, mb_strlen($slug));
    }
}
