<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request, string $locale)
    {
        $flow = $request->query('flow', 'register'); // register | petition

        // store context for callback
        if ($flow === 'petition') {
            $request->session()->put('oauth_ctx', [
                'flow' => 'petition',
                'locale' => $locale,
                'petition_id' => (int) $request->query('petition_id'),
                'slug' => (string) $request->query('slug'),
            ]);
        } else {
            $request->session()->put('oauth_ctx', [
                'flow' => 'register',
                'locale' => $locale,
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, string $locale)
    {
        $ctx = $request->session()->pull('oauth_ctx', [
            'flow' => 'register',
            'locale' => $locale,
        ]);

        $google = Socialite::driver('google')->user();
        $email = strtolower(trim($google->getEmail() ?? ''));

        if (!$email) {
            return redirect("/{$locale}/register")
                ->withErrors(['email' => 'google did not provide email.']);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $google->getName() ?: 'Google User',
                'password' => bcrypt(Str::random(32)),
                'locale' => $ctx['locale'] ?? $locale,
                // if you want google users treated as verified:
                // 'email_verified_at' => now(),
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();

        // decide where to go next
        if (($ctx['flow'] ?? '') === 'petition' && !empty($ctx['petition_id']) && !empty($ctx['slug'])) {
            return redirect()->route('petition.sign.page', [
                'locale' => $ctx['locale'],
                'slug' => $ctx['slug'],
                'id' => $ctx['petition_id'],
            ])->with('oauth_logged_in', true);
        }

        return redirect("/{$locale}");
    }
}
