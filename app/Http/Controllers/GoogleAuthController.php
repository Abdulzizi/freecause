<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLevel;
use App\Support\AppLog;
use App\Support\Locale;
use App\Support\WpSso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirectForAccountDeletion(Request $request, string $locale)
    {
        $user = Auth::guard('web')->user();

        if (! $user?->isGoogleOnly()) {
            return redirect("/{$locale}/profile")->withErrors([
                'google_delete' => 'Google verification is only available for Google-only accounts.',
            ]);
        }

        if (! config('services.google.client_id') ||
            ! config('services.google.client_secret') ||
            ! config('services.google.redirect')) {
            return redirect("/{$locale}/profile")->withErrors([
                'google_delete' => 'Google verification is temporarily unavailable. Please try again later.',
            ]);
        }

        $request->session()->forget('account_delete_google_verified');
        $request->session()->put('oauth_ctx', [
            'flow' => 'delete_account',
            'locale' => $locale,
            'user_id' => $user->id,
        ]);

        return Socialite::driver('google')
            ->with(['prompt' => 'login'])
            ->redirect();
    }

    public function redirect(Request $request, string $locale)
    {
        if (! config('services.google.client_id') ||
            ! config('services.google.client_secret') ||
            ! config('services.google.redirect')) {
            AppLog::warning(
                'Google OAuth unavailable',
                'Google OAuth credentials are not configured.',
                'auth.google'
            );

            return back()->withErrors([
                'oauth' => 'Google sign-in is temporarily unavailable. Please use email and password.',
            ]);
        }

        $flow = $request->query('flow', 'register');

        if ($flow === 'petition') {
            $request->session()->put('oauth_ctx', [
                'flow' => 'petition',
                'locale' => $locale,
                'petition_id' => (int) $request->query('petition_id'),
                'slug' => (string) $request->query('slug'),
            ]);
        } else {
            $redirect = self::sanitizeRedirectPath($request->query('redirect'), "/{$locale}");

            $request->session()->put('oauth_ctx', [
                'flow' => in_array($flow, ['login', 'register'], true) ? $flow : 'register',
                'locale' => $locale,
                'redirect' => $redirect,
            ]);
        }

        // Socialite must generate both the outgoing state and its session value.
        // Overriding the URL parameter makes its callback validation fail.
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, string $locale)
    {
        $ctx = $request->session()->pull('oauth_ctx', [
            'flow' => 'register',
            'locale' => $locale,
        ]);

        try {
            $google = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            AppLog::warning(
                'Google OAuth state mismatch',
                'Session expired or replay detected',
                'auth.google'
            );

            $destination = ($ctx['flow'] ?? null) === 'delete_account' && Auth::check()
                ? "/{$locale}/profile"
                : "/{$locale}/login";

            return redirect($destination)->withErrors([
                'oauth' => 'Your session expired or the login was interrupted. Please try again.',
            ]);
        }

        $email = strtolower(trim($google->getEmail() ?? ''));

        $fullName = trim((string) ($google->getName() ?: 'Google User'));

        $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $first = $parts[0] ?? $fullName;
        $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        $destLocale = $ctx['locale'] ?? $locale;

        if (($ctx['flow'] ?? null) === 'delete_account') {
            $currentUser = Auth::guard('web')->user();
            $matchesCurrentUser = $currentUser
                && (int) ($ctx['user_id'] ?? 0) === (int) $currentUser->id
                && filled($currentUser->google_id)
                && hash_equals((string) $currentUser->google_id, (string) $google->getId())
                && hash_equals(strtolower(trim((string) $currentUser->email)), $email);

            if (! $matchesCurrentUser) {
                $request->session()->forget('account_delete_google_verified');

                return redirect("/{$destLocale}/profile")->withErrors([
                    'google_delete' => 'That Google account does not match your FreeCause account.',
                ]);
            }

            $request->session()->put('account_delete_google_verified', [
                'user_id' => $currentUser->id,
                'verified_at' => now()->timestamp,
            ]);

            return redirect("/{$destLocale}/profile")
                ->with('success', 'Google identity verified. You can now confirm account deletion.');
        }

        if (! $email) {
            AppLog::warning(
                'Google OAuth missing email',
                'Google ID: '.$google->getId(),
                'auth.google'
            );

            return redirect("/{$destLocale}/register")->withErrors(['email' => 'google did not provide email.']);
        }

        $user = User::where('email', $email)->first();
        $userLevel = UserLevel::where('name', 'user')->first();

        $newUser = false;

        if ($user && empty($user->google_id)) {
            // Re-link is only safe when a real form password was never set:
            // password_changed_at is NULL for "no password ever" (Google-only
            // accounts), and is stamped at first password set/registration.
            $hasNoRealPassword = is_null($user->password_changed_at);

            if ($hasNoRealPassword) {
                AppLog::info(
                    'Google OAuth re-linked (no password account)',
                    'User ID: '.$user->id.' | Email: '.$email,
                    'auth.google'
                );
                // fall through — google_id will be set below
            } else {
                AppLog::warning(
                    'Google OAuth email conflict — existing account not linked',
                    'Email: '.$email.' | Google ID: '.$google->getId(),
                    'auth.google'
                );

                return redirect("/{$destLocale}/login")
                    ->withErrors(['email' => 'An account with this email already exists. Please log in with your password first, then link Google from your profile.'])
                    ->withInput(['email' => $email]);
            }
        }

        if (! $user) {
            $newUser = true;

            $user = User::create([
                'name' => $fullName,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'password' => null,
                'locale' => Locale::toFull($destLocale),
                'ip' => $request->ip(),
                'level_id' => $userLevel?->id,
                'verified' => true,
            ]);

            AppLog::info(
                'User registered via Google',
                'User ID: '.$user->id.' | Email: '.$email,
                'auth.google'
            );
        }

        $user->ip = $request->ip();
        $user->locale = Locale::toFull($destLocale);

        if (! $user->first_name) {
            $user->first_name = $first;
        }
        if (! $user->last_name) {
            $user->last_name = $last;
        }

        if ($user->verified === false) {
            $user->verified = true;
        }

        $user->google_id = $google->getId();

        $user->save();
        Auth::login($user);

        if (! $newUser) {
            AppLog::info(
                'User login via Google',
                'User ID: '.$user->id.' | Email: '.$email,
                'auth.google'
            );
        }

        $request->session()->regenerate();

        if (($ctx['flow'] ?? '') === 'petition' && ! empty($ctx['petition_id']) && ! empty($ctx['slug'])) {
            $dest = route('petition.sign.page', [
                'locale' => $ctx['locale'],
                'slug' => $ctx['slug'],
                'id' => $ctx['petition_id'],
            ]);
            // route() returns absolute URL; extract path for the SSO redirect
            $dest = parse_url($dest, PHP_URL_PATH).'?'.http_build_query(['oauth_logged_in' => 1]);

            return redirect()->to(WpSso::loginUrl($user->email, $user->name, $dest));
        }

        $dest = $ctx['redirect'] ?? "/{$destLocale}";

        return redirect()->to(WpSso::loginUrl($user->email, $user->name, $dest));
    }
}
