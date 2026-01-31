<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class PetitionOauthController extends Controller
{
    public function redirect(Request $request, string $locale, string $slug, int $id)
    {
        $request->session()->put('oauth_sign', [
            'locale' => $locale,
            'petition_id' => $id,
            'slug' => $slug,
        ]);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, string $locale)
    {
        $ctx = $request->session()->pull('oauth_sign');

        if (!$ctx) {
            return redirect("/{$locale}/")->withErrors(['oauth' => 'oauth session expired. please try again.']);
        }

        $google = Socialite::driver('google')->user();
        $email = strtolower(trim($google->getEmail() ?? ''));

        if (!$email) {
            return redirect()->route('petition.show', [
                'locale' => $ctx['locale'],
                'slug' => $ctx['slug'],
                'id' => $ctx['petition_id'],
            ])->withErrors(['email' => 'google did not provide email.']);
        }

        // find/create user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $google->getName() ?: 'Google User',
                'password' => bcrypt(str()->random(32)),
                'locale' => $ctx['locale'],
                // optional: mark verified
                // 'email_verified_at' => now(),
            ]
        );

        Auth::login($user);

        return redirect()->route('petition.sign.page', [
            'locale' => $ctx['locale'],
            'slug' => $ctx['slug'],
            'id' => $ctx['petition_id'],
        ]);
    }
}
