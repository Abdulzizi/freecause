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
        // where user came from (register/login)
        $request->session()->put('oauth_intended', url()->previous());

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, string $locale)
    {
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
                'locale' => $locale,
                // recommended if you treat google as verified:
                // 'email_verified_at' => now(),
            ]
        );

        Auth::login($user);

        $intended = $request->session()->pull('oauth_intended');
        return redirect()->to($intended ?: "/{$locale}");
    }
}
