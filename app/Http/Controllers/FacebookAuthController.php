<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class FacebookAuthController extends Controller
{
    public function redirect(Request $request, string $locale)
    {
        $flow = $request->query('flow', 'register');

        $request->session()->put('oauth_ctx', [
            'flow' => $flow,
            'locale' => $locale,
            'petition_id' => $request->query('petition_id'),
            'slug' => $request->query('slug'),
        ]);

        return Socialite::driver('facebook')
            ->scopes(['email'])
            ->redirect();
    }

    public function callback(Request $request, string $locale)
    {
    if ($request->has('error')) {
        return redirect("/{$locale}/login")
            ->withErrors(['oauth' => 'Facebook login was cancelled.']);
    }

    $ctx = $request->session()->pull('oauth_ctx', [
        'flow' => 'register',
        'locale' => $locale,
    ]);

    try {
        $facebookUser = Socialite::driver('facebook')->user();
    } catch (\Exception $e) {
        return redirect("/{$locale}/login")
            ->withErrors(['oauth' => 'Facebook authentication failed.']);
    }

    $email = strtolower(trim($facebookUser->getEmail() ?? ''));

    if (!$email) {
        return redirect("/{$locale}/register")
            ->withErrors(['email' => 'Facebook did not provide email.']);
    }

        $fullName = trim((string) ($facebookUser->getName() ?: 'Facebook User'));

        $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $first = $parts[0] ?? $fullName;
        $last  = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $fullName,
                'first_name' => $first,
                'last_name' => $last,
                'password' => bcrypt(Str::random(32)),
                'locale' => $this->toLocaleFull($locale),
                'ip' => $request->ip(),
                'level' => 'user',
                'verified' => true,
            ]
        );

        $user->ip = $request->ip();
        $user->locale = $this->toLocaleFull($locale);
        $user->verified = true;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect("/{$locale}");
    }

    private function toLocaleFull(string $locale): string
    {
        return [
            'en' => 'en_US',
            'fr' => 'fr_FR',
            'it' => 'it_IT',
        ][$locale] ?? 'en_US';
    }
}
