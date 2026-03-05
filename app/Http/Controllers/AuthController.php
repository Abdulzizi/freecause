<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Spam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyAccountMail;
use App\Models\UserLevel;
use App\Support\AppLog;
use App\Support\Settings;
use App\Support\Locale;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister(string $locale)
    {
        return view('auth.register', compact('locale'));
    }

    public function profile()
    {
        return view('auth.profile');
    }

    public function register(Request $request, string $locale)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'surname' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:72'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'agree_terms' => ['accepted'],
        ]);

        $fullName = trim($data['name'] . ' ' . $data['surname']);

        $userLevel = UserLevel::where('name', 'user')->first();

        if (Spam::isSpam($data['email'])) {
            Spam::log('register', $data['email']);

            AppLog::warning(
                'Spam registration attempt',
                'Email: ' . $data['email'],
                'auth.register'
            );

            toast('Spam detected.', 'error');
            return back()->withInput();
        }

        if (Spam::rateLimit('register')) {

            AppLog::warning(
                'Registration rate limit exceeded',
                'IP: ' . $request->ip(),
                'auth.register'
            );

            toast('Too many attempts. Please try again later.', 'error');
            return back()->withInput();
        }

        $smtpEnabled = Settings::get('smtp_enabled', false);

        $token = $smtpEnabled ? Str::random(64) : null;

        $user = User::create([
            'name' => $fullName,
            'first_name' => $data['name'],
            'last_name' => $data['surname'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'locale' => Locale::toFull($locale),
            'ip' => $request->ip(),
            'level_id' => $userLevel?->id,
            'nickname' => $data['nickname'] ?? null,
            'city' => $data['city'] ?? null,

            'verified' => $smtpEnabled ? false : true,
            'verification_token' => $token,
        ]);

        AppLog::info(
            'User registered',
            'User ID: ' . $user->id . ' | Email: ' . $user->email,
            'auth.register'
        );

        // Mail::to($user->email)->queue(
        //     new VerifyAccountMail($user, $locale)
        // );

        if ($smtpEnabled) {
            try {
                Mail::to($user->email)->send(new VerifyAccountMail($user, $locale));
            } catch (\Exception $e) {
                AppLog::error(
                    'Mail sending failed',
                    $e->getMessage(),
                    'auth.register'
                );
            }

            return redirect()
                ->to("/{$locale}/login")
                ->with('success', 'Please check your email to verify your account.');
        }

        auth()->login($user);
        $request->session()->regenerate();

        toast('Account created successfully.', 'success');

        return redirect()->to("/{$locale}");
    }

    public function login(Request $request, string $locale)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // $redirect = $request->input('redirect');
            // if ($redirect) {
            //     return redirect()->to($redirect);
            // }

            $redirect = $request->input('redirect');
            if ($redirect && str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
                return redirect()->to($redirect);
            }

            $user = Auth::user();

            if (!$user->verified) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Please verify your email first.'
                ]);
            }

            if ($user->hasLevel('banned')) {
                AppLog::warning(
                    'Banned user attempted login',
                    'User ID: ' . $user->id . ' | IP: ' . $request->ip(),
                    'auth.login'
                );

                Auth::logout();
                toast('Your account has been suspended.', 'error');
                return back()->withInput();
            }

            // if ($user && \Schema::hasColumn('users', 'ip')) {
            //     $user->ip = $request->ip();
            //     $user->save();
            // }

            $user->ip = $request->ip();
            $user->save();

            return redirect()->intended("/{$locale}");
        }

        return back()->withErrors(['email' => 'invalid credentials'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->regenerateToken();

        $locale = $request->input('locale', 'en');

        return redirect()->to("/{$locale}");
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
            'new_password' => ['nullable', 'string', 'min:6', 'max:72', 'confirmed'],
        ]);

        $first = trim($data['first_name']);
        $last  = trim($data['last_name']);

        $u->first_name = $first;
        $u->last_name  = $last;
        $u->name       = trim($first . ' ' . $last);

        $u->nickname = $data['nickname'] ?? null;
        $u->city     = $data['city'] ?? null;
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

            $u->email = $newEmail;

            if (Settings::get('smtp_enabled', false)) {
                $u->verified = false;
                $u->verification_token = Str::random(64);
                $u->verification_token_sent_at = now();

                try {
                    Mail::to($u->email)->send(new VerifyAccountMail($u, $locale));
                } catch (\Exception $e) {
                    AppLog::error(
                        'Mail sending failed (profile update)',
                        $e->getMessage(),
                        'auth.profile'
                    );
                }
            }
        }

        if (!empty($data['new_password'])) {

            if (!Hash::check($data['current_password'], $u->password)) {
                return back()
                    ->withInput()
                    ->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $u->password = Hash::make($data['new_password']);
        }

        $u->save();

        toast('Profile updated successfully.', 'success');

        return back();
    }

    public function delete(Request $request, string $locale)
    {
        $request->validate([
            'confirm_delete' => ['required', 'in:1'],
        ]);

        $u = Auth::guard('web')->user();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AppLog::warning(
            'User account deleted',
            'User ID: ' . $u->id . ' | Email: ' . $u->email,
            'auth.delete'
        );

        $u->delete();

        return redirect()->to("/{$locale}")->with('success', 'Account deleted successfully.');
    }

    public function verify(string $locale, string $token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            toast('Invalid or expired verification link.', 'error');
            return redirect()->to("/{$locale}");
        }

        if ($user->verification_token_sent_at && $user->verification_token_sent_at->lt(now()->subHours(48))) {
            toast('Verification link expired. Please request a new one.', 'error');
            return redirect()->to("/{$locale}/resend-verification");
        }

        $user->verified = true;
        $user->verification_token = null;
        $user->save();

        //* AUTO LOGIN AFTER VERIFY
        // Auth::login($user);
        // request()->session()->regenerate();

        return redirect()->to("/{$locale}")->with('success', 'Your account has been verified.');
    }

    public function unlinkGoogle(Request $request)
    {
        $user = auth()->user();

        if (!$user->password) {
            return back()->withErrors(['error' => 'Set a password before unlinking Google.']);
        }

        $user->google_id = null;
        $user->save();

        return back()->with('success', 'Google account unlinked.');
    }

    public function unlinkFacebook(Request $request)
    {
        $user = auth()->user();

        if (!$user->password) {
            return back()->withErrors(['error' => 'Set a password before unlinking Facebook.']);
        }

        $user->facebook_id = null;
        $user->save();

        return back()->with('success', 'Facebook account unlinked.');
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
            ? back()->with('success', __($status))
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
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();

                event(new PasswordReset($user));
            }
        );

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

        if (!Settings::get('smtp_enabled', false)) {
            return back()->withErrors([
                'email' => 'Email verification is not enabled.'
            ]);
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'User not found.'
            ]);
        }

        if ($user->verified) {
            return back()->withErrors([
                'email' => 'This account is already verified.'
            ]);
        }

        $user->verification_token = Str::random(64);
        $user->verification_token_sent_at = now();
        $user->save();

        try {
            Mail::to($user->email)->send(
                new VerifyAccountMail($user, $locale)
            );

            AppLog::info(
                'Verification email resent',
                'User ID: ' . $user->id . ' | Email: ' . $user->email,
                'auth.verification'
            );
        } catch (\Exception $e) {

            AppLog::error(
                'Verification resend failed',
                $e->getMessage(),
                'auth.verification'
            );

            return back()->withErrors([
                'email' => 'Failed to send email. Please try again later.'
            ]);
        }

        return back()->with('success', 'Verification email has been resent.');
    }
}
