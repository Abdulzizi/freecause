<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLevel;
use App\Support\AppLog;
use App\Support\Locale;
use App\Support\WpSso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request, string $locale)
    {
        $flow = $request->query('flow', 'register'); // register | petition

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

        $fullName = trim((string) ($google->getName() ?: 'Google User'));

        $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $first = $parts[0] ?? $fullName;
        $last  = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        $destLocale = $ctx['locale'] ?? $locale;

        if (!$email) {
            AppLog::warning(
                'Google OAuth missing email',
                'Google ID: ' . $google->getId(),
                'auth.google'
            );

            return redirect("/{$destLocale}/register")->withErrors(['email' => 'google did not provide email.']);
        }

        $user = User::where('email', $email)->first();
        $userLevel = UserLevel::where('name', 'user')->first();

        $newUser = false;

        if ($user && empty($user->google_id)) {
            // If user has no real password (never set one, no facebook either)
            // they registered via Google and then unlinked — re-link automatically
            $hasNoRealPassword = empty($user->facebook_id)
                && is_null($user->password_changed_at);

            if ($hasNoRealPassword) {
                AppLog::info(
                    'Google OAuth re-linked (no password account)',
                    'User ID: ' . $user->id . ' | Email: ' . $email,
                    'auth.google'
                );
                // fall through — google_id will be set below
            } else {
                AppLog::warning(
                    'Google OAuth email conflict — existing account not linked',
                    'Email: ' . $email . ' | Google ID: ' . $google->getId(),
                    'auth.google'
                );
                return redirect("/{$destLocale}/login")
                    ->withErrors(['email' => 'An account with this email already exists. Please log in with your password first, then link Google from your profile.'])
                    ->withInput(['email' => $email]);
            }
        }

        if (!$user) {
            $newUser = true;

            $user = User::create([
                'name' => $fullName,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'locale' => Locale::toFull($destLocale),
                'ip' => $request->ip(),
                'level_id' => $userLevel?->id,
                'verified' => true,
            ]);

            AppLog::info(
                'User registered via Google',
                'User ID: ' . $user->id . ' | Email: ' . $email,
                'auth.google'
            );
        }

        $user->ip = $request->ip();
        $user->locale = Locale::toFull($destLocale);

        if (!$user->first_name) $user->first_name = $first;
        if (!$user->last_name)  $user->last_name = $last;

        if ($user->verified === false) {
            $user->verified = true;
        }

        $user->google_id = $google->getId();

        $user->save();
        Auth::login($user);

        if (!$newUser) {
            AppLog::info(
                'User login via Google',
                'User ID: ' . $user->id . ' | Email: ' . $email,
                'auth.google'
            );
        }

        $request->session()->regenerate();

        if (($ctx['flow'] ?? '') === 'petition' && !empty($ctx['petition_id']) && !empty($ctx['slug'])) {
            $dest = route('petition.sign.page', [
                'locale' => $ctx['locale'],
                'slug' => $ctx['slug'],
                'id' => $ctx['petition_id'],
            ]);
            // route() returns absolute URL; extract path for the SSO redirect
            $dest = parse_url($dest, PHP_URL_PATH) . '?' . http_build_query(['oauth_logged_in' => 1]);
            return redirect()->to(WpSso::loginUrl($user->email, $user->name, $dest));
        }

        return redirect()->to(WpSso::loginUrl($user->email, $user->name, "/{$destLocale}"));
    }
}