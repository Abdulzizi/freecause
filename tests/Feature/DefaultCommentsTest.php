<?php

namespace Tests\Feature;

use App\Support\DefaultComments;
use Tests\TestCase;

class DefaultCommentsTest extends TestCase
{
    public function test_it_includes_the_audited_boilerplate_variants(): void
    {
        $list = DefaultComments::forLocales(['en', 'pl', 'el']);

        $this->assertContains('I support this petition', $list);
        $this->assertContains('I support this petition.', $list);
        $this->assertContains('Podpisuje się pod tą petycją', $list);
        $this->assertContains('Podpisuję się pod tą petycją', $list);
        $this->assertContains('Υποστηρίζω αυτήν την αίτηση', $list);
    }

    public function test_it_returns_a_unique_list(): void
    {
        $list = DefaultComments::forLocales(['en', 'en', 'pl']);

        $this->assertSame(array_values(array_unique($list)), $list);
    }
}
