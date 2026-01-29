<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    protected array $allowedLocales = ['en', 'fr', 'it', 'es', 'de', 'pt', 'nl'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale');

        if (! in_array($locale, $this->allowedLocales)) {
            abort(404);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
