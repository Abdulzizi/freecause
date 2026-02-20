<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AppLog;
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
                'Google ID: '.$google->getId(),
                'auth.google'
            );

            return redirect("/{$destLocale}/register")->withErrors(['email' => 'google did not provide email.']);
        }

        // $user = User::firstOrCreate(
        //     ['email' => $email],
        //     [
        //         'name' => $fullName,
        //         'first_name' => $first,
        //         'last_name' => $last,
        //         'password' => bcrypt(Str::random(32)),
        //         'locale' => $this->toLocaleFull($destLocale),
        //         'ip' => $request->ip(),
        //         'level' => 'user',
        //         'verified' => true,
        //     ]
        // );

        $user = User::where('email', $email)->first();

        $newUser = false;

        if (!$user) {
            $newUser = true;

            $user = User::create([
                'name' => $fullName,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'locale' => $this->toLocaleFull($destLocale),
                'ip' => $request->ip(),
                'level' => 'user',
                'verified' => true,
            ]);

            AppLog::info(
                'User registered via Google',
                'User ID: '.$user->id.' | Email: '.$email,
                'auth.google'
            );
        }

        $user->ip = $request->ip();
        $user->locale = $this->toLocaleFull($destLocale);

        if (!$user->first_name) $user->first_name = $first;
        if (!$user->last_name)  $user->last_name = $last;

        if ($user->verified === false) {
            $user->verified = true;
        }

        $user->save();
        Auth::login($user);

        if (!$newUser) {
            AppLog::info(
                'User login via Google',
                'User ID: '.$user->id .' | Email: '.$email,
                'auth.google'
            );
        }

        $request->session()->regenerate();

        if (($ctx['flow'] ?? '') === 'petition' && !empty($ctx['petition_id']) && !empty($ctx['slug'])) {
            return redirect()->route('petition.sign.page', [
                'locale' => $ctx['locale'],
                'slug' => $ctx['slug'],
                'id' => $ctx['petition_id'],
            ])->with('oauth_logged_in', true);
        }

        return redirect("/{$locale}");
    }

    private function toLocaleFull(string $locale): string
    {
        $map = [
            'en' => 'en_US',
            'fr' => 'fr_FR',
            'it' => 'it_IT',
            'da' => 'da_DK',
        ];

        return $map[$locale] ?? 'en_US';
    }
}
