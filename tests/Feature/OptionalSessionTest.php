<?php

namespace Tests\Feature;

use App\Http\Middleware\OptionalSession;
use Illuminate\Http\Request;
use Tests\TestCase;

class OptionalSessionTest extends TestCase
{
    private function passThrough(Request $request): Request
    {
        $seen = null;

        (new OptionalSession)->handle($request, function ($req) use (&$seen) {
            $seen = $req;

            return response('ok');
        });

        return $seen;
    }

    public function test_cookie_less_get_uses_the_stateless_fast_path(): void
    {
        $seen = $this->passThrough(Request::create('/en/petitions', 'GET'));

        $this->assertTrue($seen->attributes->get('stateless_render'));
        $this->assertFalse($seen->hasSession());
    }

    public function test_post_is_never_stateless_so_csrf_always_runs(): void
    {
        $seen = $this->passThrough(Request::create('/en/petitions', 'POST'));

        $this->assertNull($seen->attributes->get('stateless_render'));
        $this->assertTrue($seen->hasSession());
    }

    public function test_request_with_cookie_is_never_stateless(): void
    {
        $request = Request::create('/en/petitions', 'GET');
        $request->cookies->set(config('session.cookie'), 'opaque');

        $seen = $this->passThrough($request);

        $this->assertNull($seen->attributes->get('stateless_render'));
        $this->assertTrue($seen->hasSession());
    }
}
