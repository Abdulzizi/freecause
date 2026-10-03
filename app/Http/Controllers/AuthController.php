<?php

namespace App\Http\Controllers;

use App\Mail\VerifyAccountMail;
use App\Models\Petition;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\AppLog;
use App\Support\PetitionCache;
use App\Support\Settings;
use App\Support\Spam;
use App\Support\WpSso;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin(string $locale)
    {
        if (Auth::check()) {
            return redirect()->to("/{$locale}");
        }

        return view('auth.login');
    }

    public function showRegister(string $locale)
    {
        if (Auth::check()) {
            return redirect()->to("/{$locale}");
        }

        return view('auth.register', compact('locale'));
    }

    public function profile()
    {
        return view('auth.profile');
    }

    public function register(Request $request, string $locale)
    {
        $request->merge(['email' => strtolower(trim((string) $request->email))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'surname' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'nickname' => ['nullable', 'regex:/^[A-Za-z0-9]{3,32}$/', 'unique:users,nickname'],
            'city' => ['nullable', 'string', 'max:80'],
            'agree_terms' => ['accepted'],
        ]);

        $fullName = trim($data['name'].' '.$data['surname']);

        $userLevel = UserLevel::where('name', 'user')->first();

        if (Spam::isSpam($data['email'])) {
            Spam::log('register', $data['email']);

            AppLog::warning(
                'Spam registration attempt',
                'Email: '.$data['email'],
                'auth.register'
            );

            toast('Spam detected.', 'error');

            return back()->withInput();
        }

        if (Spam::rateLimit('register')) {

            AppLog::warning(
                'Registration rate limit exceeded',
                'IP: '.$request->ip(),
                'auth.register'
            );

            toast('Too many attempts. Please try again later.', 'error');

            return back()->withInput();
        }

        $smtpEnabled = Settings::get('smtp_enabled', false);
        $requireVerif = Settings::get('require_email_verification', false, 'global');

        $needsVerif = (bool) $requireVerif;
        $token = $needsVerif ? Str::random(64) : null;

        $user = User::create([
            'name' => $fullName,
            'first_name' => $data['name'],
            'last_name' => $data['surname'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            // Marks that a real (form-based) password exists at first set.
            'password_changed_at' => now(),
            'locale' => $locale,
            'ip' => $request->ip(),
            'level_id' => $userLevel?->id,
            'nickname' => $data['nickname'] ?? null,
            'city' => $data['city'] ?? null,

            'verified' => $needsVerif ? false : true,
            'verification_token' => $token,
            'verification_token_sent_at' => $needsVerif ? now() : null,
        ]);

        AppLog::info(
            'User registered',
            'User ID: '.$user->id.' | Email: '.$user->email,
            'auth.register'
        );

        if ($needsVerif && $smtpEnabled) {
            try {
                Mail::to($user->email)->queue(new VerifyAccountMail($user, $locale));
            } catch (\Exception $e) {
                AppLog::error(
                    'Mail sending failed',
                    $e->getMessage(),
                    'auth.register'
                );

                return redirect()
                    ->to("/{$locale}/login")
                    ->with('error', 'Registration failed. Please contact support or try again later.');
            }

            return redirect()
                ->to("/{$locale}/login")
                ->with('success', 'Please check your email to verify your account.');
        }

        if ($needsVerif && ! $smtpEnabled) {
            return redirect()
                ->to("/{$locale}/login")
                ->with('success', 'Your account is pending verification. Please contact support.');
        }

        auth()->login($user);
        $request->session()->regenerate();

        toast('Account created successfully.', 'success');

        $dest = self::sanitizeRedirectPath($request->input('redirect'), "/{$locale}");

        return redirect()->to(WpSso::loginUrl($user->email, $user->name, $dest));
    }

    public function login(Request $request, string $locale)
    {
        $request->merge(['email' => strtolower(trim((string) $request->email))]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            if (! $user->verified) {
                return back()->withErrors(['email' => 'Please verify your email first.'])->onlyInput('email', 'auth_form');
            }

            if ($user->hasLevel('banned')) {
                AppLog::warning(
                    'Banned user attempted login',
                    'User ID: '.$user->id.' | IP: '.$request->ip(),
                    'auth.login'
                );
                toast('Your account has been suspended.', 'error');

                return back()->withInput();
            }
        }

        $legacyHash = $user && filled($user->password) && str_contains($user->password, ':');

        // Try standard bcrypt login first.
        try {
            $loggedIn = ! $legacyHash
                && Auth::guard('web')->attempt($credentials, $remember);
        } catch (\RuntimeException) {
            $loggedIn = false;
        }

        // Fallback: legacy md5(md5(password).salt):salt hash for migrated old users
        if (! $loggedIn && $legacyHash) {
            [$hash, $salt] = explode(':', $user->password, 2);
            if (md5(md5($credentials['password']).$salt) === $hash) {
                // Rehash to bcrypt and save
                $user->password = Hash::make($credentials['password']);
                $user->password_changed_at = now();
                $user->save();
                Auth::guard('web')->login($user, $remember);
                $loggedIn = true;
            }
        }

        if ($loggedIn) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->ip = $request->ip();
            $user->save();

            $dest = self::sanitizeRedirectPath($request->input('redirect'), '');
            if ($dest === '') {
                $dest = $request->session()->pull('url.intended', "/{$locale}");
            }

            return redirect()->to(WpSso::loginUrl($user->email, $user->name, $dest));
        }

        return back()->withErrors(['email' => 'invalid credentials'])->onlyInput('email', 'auth_form');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $locale = $request->input('locale', 'en');

        return redirect()->to(WpSso::logoutUrl("/{$locale}"));
    }

    public function updateProfile(Request $request, string $locale)
    {
        $u = Auth::guard('web')->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'identify_mode' => ['nullable', Rule::in(['full', 'name', 'nick'])],

            'new_email' => ['nullable', 'email', 'max:190'],
            'new_email_confirmation' => ['nullable', 'same:new_email'],

            'current_password' => ['required_with:new_password'],
            'new_password' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        $first = trim($data['first_name']);
        $last = trim($data['last_name']);

        $u->first_name = $first;
        $u->last_name = $last;
        $u->name = trim($first.' '.$last);

        $u->nickname = $data['nickname'] ?? null;
        $u->city = $data['city'] ?? null;
        $u->identify_mode = $data['identify_mode'] ?? ($u->identify_mode ?? 'full');

        $newEmail = strtolower(trim($data['new_email'] ?? ''));

        if ($newEmail !== '') {
            $exists = User::where('email', $newEmail)
                ->where('id', '!=', $u->id)
                ->exists();

            if ($exists) {
                return back()
                    ->withInput()
                    ->withErrors(['new_email' => 'This email is already taken.']);
            }

            if (! Settings::get('smtp_enabled', false)) {
                // Without an active mail system we cannot verify the new
                // address, which would allow taking over a Google account
                // claiming that email. Reject the change instead.
                return back()
                    ->withInput()
                    ->withErrors(['new_email' => 'Email changes require an active mail system. Please try again later.']);
            }

            $u->email = $newEmail;
            $u->verified = false;
            $u->verification_token = Str::random(64);
            $u->verification_token_sent_at = now();

            try {
                Mail::to($u->email)->queue(new VerifyAccountMail($u, $locale));
            } catch (\Exception $e) {
                AppLog::error(
                    'Mail sending failed (profile update)',
                    $e->getMessage(),
                    'auth.profile'
                );
            }
        }

        if (! empty($data['new_password'])) {

            if (! Hash::check($data['current_password'], $u->password)) {
                return back()
                    ->withInput()
                    ->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $u->password = Hash::make($data['new_password']);
            $u->password_changed_at = now();
        }

        $u->save();

        toast('Profile updated successfully.', 'success');

        return back();
    }

    public function delete(Request $request, string $locale)
    {
        $request->validate([
            'password' => ['required', 'current_password:web'],
            'confirm_delete' => ['required', 'in:1'],
        ]);

        $u = Auth::guard('web')->user();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AppLog::warning(
            'User account deleted',
            'User ID: '.$u->id.' | Email: '.$u->email,
            'auth.delete'
        );

        try {
            // WP is integrated via wp-load.php ($wpdb), never via the Laravel
            // database connection. Only attempt the lookup when WP bootstrap
            // is actually available; otherwise skip with a log entry.
            $wpLoad = base_path('public/magazine/wp-load.php');

            if (is_file($wpLoad)) {
                require_once $wpLoad;

                $wpUserId = $GLOBALS['wpdb']->get_var(
                    $GLOBALS['wpdb']->prepare(
                        'SELECT ID FROM '.$GLOBALS['wpdb']->users.' WHERE user_email = %s OR user_login = %s',
                        $u->email,
                        $u->email
                    )
                );

                if ($wpUserId) {
                    wp_delete_user((int) $wpUserId);
                }
            } else {
                Log::warning('WP user deletion skipped: wp-load.php not found');
            }
        } catch (\Throwable $e) {
            Log::error('WP user deletion failed: '.$e->getMessage());
        }

        $u->delete();

        return redirect()->to("/{$locale}")->with('success', 'Account deleted successfully.');
    }

    public function verify(string $locale, string $token)
    {
        $user = User::where('verification_token', $token)->first();

        if (! $user) {
            toast('Invalid or expired verification link.', 'error');

            return redirect()->to("/{$locale}");
        }

        if ($user->verified) {
            toast('Account is already verified.', 'info');

            return redirect()->to("/{$locale}");
        }

        if ($user->verification_token_sent_at && $user->verification_token_sent_at->lt(now()->subHours(48))) {
            toast('Verification link expired. Please request a new one.', 'error');

            return redirect()->to("/{$locale}/resend-verification");
        }

        DB::transaction(function () use ($user) {
            // Lock the row and atomically gate on `verified` so concurrent
            // double-clicks verify exactly once and claim pending signatures
            // exactly once.
            $locked = User::whereKey($user->id)->where('verified', false)->lockForUpdate()->first();

            if (! $locked) {
                return;
            }

            $pending = DB::table('signatures')
                ->where('user_id', $user->id)
                ->where('confirmed', false)
                ->lockForUpdate()
                ->selectRaw('petition_id, COUNT(*) as total')
                ->groupBy('petition_id')
                ->get();

            $locked->verified = true;
            $locked->verification_token = null;
            $locked->save();

            DB::table('signatures')
                ->where('user_id', $user->id)
                ->where('confirmed', false)
                ->update(['confirmed' => true, 'updated_at' => now()]);

            foreach ($pending as $row) {
                DB::table('petitions')->where('id', $row->petition_id)->increment('signature_count', $row->total);

                if ($petition = Petition::find($row->petition_id)) {
                    PetitionCache::touch($petition);
                }
            }
        });

        // * AUTO LOGIN AFTER VERIFY
        // Auth::login($user);
        // request()->session()->regenerate();

        return redirect()->to("/{$locale}")->with('success', 'Your account has been verified.');
    }

    public function unlinkGoogle(Request $request)
    {
        $user = auth()->user();

        if (! $user->password) {
            return back()->withErrors(['error' => 'Set a password before unlinking Google.']);
        }

        $user->google_id = null;
        $user->save();

        return back()->with('success', 'Google account unlinked.');
    }

    public function showForgotPassword(string $locale)
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request, string $locale)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(string $locale, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request, string $locale)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->password = Hash::make($password);
                    // A set password implies password_changed_at is set (never NULL).
                    $user->password_changed_at = now();
                    $user->save();

                    event(new PasswordReset($user));
                }
            );
        } catch (\Throwable $e) {
            $status = Password::INVALID_TOKEN;
        }

        return $status === Password::PASSWORD_RESET
            ? redirect("/{$locale}/login")->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResendVerification(string $locale)
    {
        return view('auth.resend-verification', compact('locale'));
    }

    public function resendVerification(Request $request, string $locale)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (! Settings::get('smtp_enabled', false)) {
            return back()->withErrors([
                'email' => 'Email verification is not enabled.',
            ]);
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (! $user || $user->verified) {
            return back()->with('success', 'If the account needs activation, a new email has been sent.');
        }

        $user->verification_token = Str::random(64);
        $user->verification_token_sent_at = now();
        $user->save();

        try {
            Mail::to($user->email)->queue(
                new VerifyAccountMail($user, $locale)
            );

            AppLog::info(
                'Verification email resent',
                'User ID: '.$user->id.' | Email: '.$user->email,
                'auth.verification'
            );
        } catch (\Exception $e) {

            AppLog::error(
                'Verification resend failed',
                $e->getMessage(),
                'auth.verification'
            );

            return back()->withErrors([
                'email' => 'Failed to send email. Please try again later.',
            ]);
        }

        return back()->with('success', 'If the account needs activation, a new email has been sent.');
    }
}
