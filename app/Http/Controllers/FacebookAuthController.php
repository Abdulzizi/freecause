<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLevel;
use App\Support\AppLog;
use App\Support\Locale;
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
            AppLog::warning(
                'Facebook OAuth cancelled',
                'IP: ' . $request->ip(),
                'auth.facebook'
            );

            return redirect("/{$locale}/login")->withErrors(['oauth' => 'Facebook login was cancelled.']);
        }

        $ctx = $request->session()->pull('oauth_ctx', [
            'flow' => 'register',
            'locale' => $locale,
        ]);

        try {
            $facebookUser = Socialite::driver('facebook')->user();
        } catch (\Exception $e) {
            AppLog::error(
                'Facebook OAuth failed',
                $e->getMessage() . ' | IP: ' . $request->ip(),
                'auth.facebook'
            );

            return redirect("/{$locale}/login")->withErrors(['oauth' => 'Facebook authentication failed.']);
        }

        $email = strtolower(trim($facebookUser->getEmail() ?? ''));

        if (!$email) {
            AppLog::warning(
                'Facebook OAuth missing email',
                'Facebook ID: ' . $facebookUser->getId(),
                'auth.facebook'
            );

            return redirect("/{$locale}/register")->withErrors(['email' => 'Facebook did not provide email.']);
        }

        $fullName = trim((string) ($facebookUser->getName() ?: 'Facebook User'));

        $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $first = $parts[0] ?? $fullName;
        $last  = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        $user = User::where('email', $email)->first();

        $userLevel = UserLevel::where('name', 'user')->first();
        $newUser = false;

        if ($user && empty($user->facebook_id)) {
            return redirect("/{$locale}/login")
                ->withErrors(['email' => 'An account with this email already exists. Please log in with your password first, then link Facebook from your profile.'])
                ->withInput(['email' => $email]);
        }

        if (!$user) {
            $newUser = true;

            $user = User::create([
                'name' => $fullName,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'locale' => Locale::toFull($locale),
                'ip' => $request->ip(),
                'level_id' => $userLevel?->id,
                'verified' => true,
            ]);

            AppLog::info(
                'User registered via Facebook',
                'User ID: ' . $user->id . ' | Email: ' . $email,
                'auth.facebook'
            );
        }

        $user->ip = $request->ip();
        $user->locale = Locale::toFull($locale);
        $user->verified = true;

        $user->facebook_id = $facebookUser->getId();

        $user->save();

        Auth::login($user);

        if (!$newUser) {
            AppLog::info(
                'User login via Facebook',
                'User ID: ' . $user->id,
                'auth.facebook'
            );
        }

        $request->session()->regenerate();

        if (($ctx['flow'] ?? '') === 'petition' && !empty($ctx['petition_id']) && !empty($ctx['slug'])) {
            return redirect()->route('petition.sign.page', [
                'locale' => $ctx['locale'],
                'slug'   => $ctx['slug'],
                'id'     => $ctx['petition_id'],
            ]);
        }

        return redirect("/{$locale}");
    }
}
