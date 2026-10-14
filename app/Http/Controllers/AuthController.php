<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Mail\VerifyAccountMail;
use App\Models\Language;
use App\Models\User;
use App\Models\UserLevel;
use App\Services\PetitionStats;
use App\Services\WordPressUserDeletionService;
use App\Support\AppLog;
use App\Support\Countries;
use App\Support\Locale;
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
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly WordPressUserDeletionService $wordPressUserDeletion) {}

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
        $languages = Language::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['code', 'name']);

        return view('auth.profile', [
            'countries' => Countries::all(),
            'languages' => $languages,
        ]);
    }

    public function register(RegisterRequest $request, string $locale)
    {
        $data = $request->validated();

        $fullName = trim($data['name'].' '.$data['surname']);

        $userLevel = UserLevel::where('name', 'user')->first();

        if (Spam::isSpam($data['email'])) {
            Spam::log('register', $data['email']);

            AppLog::warning(
                'Spam registration attempt',
                'Email: '.$data['email'],
                'auth.register'
            );

            toast(__('auth.err_spam'), 'error');

            return back()->withInput();
        }

        if (Spam::rateLimit('register')) {

            AppLog::warning(
                'Registration rate limit exceeded',
                'IP: '.$request->ip(),
                'auth.register'
            );

            toast(__('auth.err_throttled'), 'error');

            return back()->withInput();
        }

        $smtpEnabled = Settings::get('smtp_enabled', false);
        $requireVerif = Settings::get('require_email_verification', false, 'global');

        $needsVerif = (bool) $requireVerif;
        $token = $needsVerif ? Str::random(64) : null;

        try {
            $user = User::create([
                'name' => $fullName,
                'first_name' => $data['name'],
                'last_name' => $data['surname'],
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
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
        } catch (\Throwable $e) {
            AppLog::error(
                'User registration failed',
                'Email: '.$data['email'].' | '.$e->getMessage(),
                'auth.register'
            );

            return back()->withInput()->withErrors([
                'email' => __('auth.register_failed'),
            ]);
        }

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

                $this->markPendingVerification($request, $user, $locale);

                return redirect()
                    ->route('verification.notice', ['locale' => $locale])
                    ->with('error', __('auth.register_failed'));
            }

            $this->markPendingVerification($request, $user, $locale);

            return redirect()
                ->route('verification.notice', ['locale' => $locale])
                ->with('success', __('auth.register_check_email'));
        }

        if ($needsVerif && ! $smtpEnabled) {
            $this->markPendingVerification($request, $user, $locale);

            return redirect()
                ->route('verification.notice', ['locale' => $locale])
                ->with('success', __('auth.register_pending'));
        }

        auth()->login($user);
        $request->session()->regenerate();

        toast(__('auth.register_success'), 'success');

        $dest = self::sanitizeRedirectPath($request->input('redirect'), "/{$locale}");

        if (! $user->hasCompleteProfile()) {
            $dest = self::profileCompletionTarget($locale, $dest);
        }

        return redirect()->to(WpSso::loginUrl($user->email, $user->name, $dest));
    }

    public function login(LoginRequest $request, string $locale)
    {
        $credentials = $request->validated();

        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            if ($user->hasLevel('banned')) {
                AppLog::warning(
                    'Banned user attempted login',
                    'User ID: '.$user->id.' | IP: '.$request->ip(),
                    'auth.login'
                );
                toast(__('auth.err_suspended'), 'error');

                return back()->withInput();
            }
        }

        $legacyHash = $user && filled($user->password) && str_contains($user->password, ':');

        try {
            $loggedIn = ! $legacyHash
                && Auth::guard('web')->attempt($credentials, $remember);
        } catch (\RuntimeException) {
            $loggedIn = false;
        }

        if (! $loggedIn && $legacyHash) {
            [$hash, $salt] = explode(':', $user->password, 2);
            if (md5(md5($credentials['password']).$salt) === $hash) {
                $user->password = Hash::make($credentials['password']);
                $user->password_changed_at = now();
                $user->save();
                Auth::guard('web')->login($user, $remember);
                $loggedIn = true;
            }
        }

        if ($loggedIn) {
            if (! $user->verified) {
                Auth::guard('web')->logout();

                $request->session()->put('verify_email', $user->email);

                return redirect()
                    ->route('verification.notice', ['locale' => $locale])
                    ->withErrors(['email' => __('auth.err_verify_first')]);
            }

            $request->session()->regenerate();

            $user = Auth::user();
            $user->ip = $request->ip();
            if (filled($user->password) && $user->password_changed_at === null) {
                $user->password_changed_at = now();
            }
            $user->save();

            $dest = self::sanitizeRedirectPath($request->input('redirect'), '');
            if ($dest === '') {
                $dest = $request->session()->pull('url.intended', '');
            }
            if ($dest === '') {
                $dest = '/'.Locale::toShort($user->locale);
            }

            if (! $user->hasCompleteProfile()) {
                $dest = self::profileCompletionTarget($locale, $dest);
            }

            return redirect()->to(WpSso::loginUrl($user->email, $user->name, $dest));
        }

        return back()->withErrors(['email' => __('auth.err_invalid_credentials')])->onlyInput('email', 'auth_form');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $locale = $request->input('locale', 'en');

        return redirect()->to(WpSso::logoutUrl("/{$locale}"));
    }

    public function updateProfile(UpdateProfileRequest $request, string $locale)
    {
        $u = Auth::guard('web')->user();

        $data = $request->validated();

        $first = trim($data['first_name']);
        $last = trim($data['last_name']);

        $u->first_name = $first;
        $u->last_name = $last;
        $u->name = trim($first.' '.$last);

        $u->nickname = $data['nickname'] ?? null;
        $u->city = $data['city'] ?? null;
        $u->country = $data['country'];
        $u->gender = $data['gender'];
        $u->locale = Locale::toFull($data['locale']);
        $u->identify_mode = $data['identify_mode'] ?? ($u->identify_mode ?? 'full');

        $newEmail = strtolower(trim($data['new_email'] ?? ''));
        $emailChanged = false;

        if ($newEmail !== '' && $newEmail !== strtolower((string) $u->email)) {
            $exists = User::where('email', $newEmail)
                ->where('id', '!=', $u->id)
                ->exists();

            if ($exists) {
                return back()
                    ->withInput()
                    ->withErrors(['new_email' => __('profile.email_taken')]);
            }

            if (! Settings::get('smtp_enabled', false)) {

                return back()
                    ->withInput()
                    ->withErrors(['new_email' => __('profile.email_needs_mail_system')]);
            }

            $u->email = $newEmail;
            $u->verified = false;
            $u->verification_token = Str::random(64);
            $u->verification_token_sent_at = now();
            $emailChanged = true;
        }

        if (! empty($data['new_password'])) {
            if ($u->hasLocalPassword() && ! Hash::check($data['current_password'] ?? '', $u->password)) {
                return back()
                    ->withInput()
                    ->withErrors(['current_password' => __('profile.current_password_incorrect')]);
            }

            $u->password = Hash::make($data['new_password']);
            $u->password_changed_at = now();
        }

        try {
            DB::transaction(fn () => $u->save());
        } catch (\Throwable $e) {
            AppLog::error(
                'Profile update failed',
                'User ID: '.$u->id.' | '.$e->getMessage(),
                'auth.profile'
            );

            return back()
                ->withInput()
                ->withErrors(['profile' => __('profile.update_failed')]);
        }

        if ($emailChanged) {
            try {
                Mail::to($u->email)->queue(new VerifyAccountMail($u, $locale));
            } catch (\Throwable $e) {
                AppLog::error(
                    'Mail sending failed (profile update)',
                    'User ID: '.$u->id.' | '.$e->getMessage(),
                    'auth.profile'
                );
            }

            toast(__('profile.email_change_pending'), 'info');
        } else {
            toast(__('profile.updated_success'), 'success');
        }

        $return = $request->query('return');

        return redirect()->to(
            self::sanitizeRedirectPath(is_string($return) ? $return : null, "/{$data['locale']}/profile")
        );
    }

    public function delete(DeleteAccountRequest $request, string $locale)
    {
        $u = Auth::guard('web')->user();

        if ($u->isGoogleOnly()) {
            $proof = $request->session()->pull('account_delete_google_verified');
            $verifiedAt = is_array($proof) ? (int) ($proof['verified_at'] ?? 0) : 0;
            $proofIsValid = is_array($proof)
                && (int) ($proof['user_id'] ?? 0) === (int) $u->id
                && $verifiedAt >= now()->subMinutes(5)->timestamp;

            if (! $proofIsValid) {
                throw ValidationException::withMessages([
                    'google_delete' => __('profile.account_delete_verify_google'),
                ]);
            }
        }

        $userId = $u->id;
        $userEmail = $u->email;

        try {
            $this->wordPressUserDeletion->delete($u);
        } catch (\Throwable $e) {
            Log::error('Account deletion aborted because WordPress cleanup failed: '.$e->getMessage());

            return back()->withErrors([
                'delete_account' => __('profile.account_delete_failed'),
            ]);
        }

        try {
            User::query()->whereKey($u->getKey())->delete();
        } catch (\Throwable $e) {
            AppLog::error(
                'Account deletion failed at local delete step',
                'User ID: '.$userId.' | '.$e->getMessage(),
                'auth.delete'
            );

            return back()->withErrors([
                'delete_account' => __('profile.account_delete_failed'),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AppLog::warning(
            'User account deleted',
            'User ID: '.$userId.' | Email: '.$userEmail,
            'auth.delete'
        );

        return redirect()->to("/{$locale}")->with('success', __('profile.account_deleted_success'));
    }

    public function verify(Request $request, string $locale, string $token)
    {
        $user = User::where('verification_token', $token)->first();

        if (! $user) {
            toast(__('auth.verify_invalid'), 'error');

            return redirect()->to("/{$locale}");
        }

        if ($user->verified) {
            toast(__('auth.verify_already'), 'info');

            return redirect()->to("/{$locale}");
        }

        if ($user->verification_token_sent_at && $user->verification_token_sent_at->lt(now()->subHours(48))) {
            toast(__('auth.verify_expired'), 'error');

            return redirect()->to("/{$locale}/resend-verification");
        }

        DB::transaction(function () use ($user) {

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

            $increments = [];
            foreach ($pending as $row) {
                $increments[(int) $row->petition_id] = (int) $row->total;
            }
            PetitionStats::incrementMany($increments);
        });

        if ($request->session()->pull('verify_autologin')) {
            $user->refresh();

            if (! $user->verified) {
                toast(__('auth.verify_invalid'), 'error');

                return redirect()->to("/{$locale}");
            }

            auth()->login($user);
            $request->session()->regenerate();
            $request->session()->forget('verify_email');

            toast(__('auth.verify_success'), 'success');

            $intended = $request->session()->pull('verify_intended');

            if ($intended && ! $user->hasCompleteProfile()) {
                $intended = self::profileCompletionTarget($locale, $intended);
            }

            return redirect()->to($intended ?: "/{$locale}");
        }

        $request->session()->forget(['verify_email', 'verify_intended']);

        return redirect()
            ->route('verification.verified', ['locale' => $locale])
            ->with('success', __('auth.verify_success'));
    }

    public function unlinkGoogle(Request $request)
    {
        $user = auth()->user();

        if (! $user->hasLocalPassword()) {
            return back()->withErrors(['google_unlink' => __('profile.google_unlink_needs_password')]);
        }

        try {
            $user->google_id = null;
            DB::transaction(fn () => $user->save());
        } catch (\Throwable $e) {
            AppLog::error(
                'Google unlink failed',
                'User ID: '.$user->id.' | '.$e->getMessage(),
                'auth.google'
            );

            return back()->withErrors(['google_unlink' => __('profile.google_unlink_failed')]);
        }

        return back()->with('success', __('profile.google_unlinked_success'));
    }

    public function showForgotPassword(string $locale)
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(EmailRequest $request, string $locale)
    {
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

    public function resetPassword(ResetPasswordRequest $request, string $locale)
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->password = Hash::make($password);
                    $user->password_changed_at = now();
                    $user->save();

                    event(new PasswordReset($user));
                }
            );
        } catch (\Throwable $e) {
            $status = Password::INVALID_TOKEN;
        }

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        $user = User::where('email', strtolower(trim((string) $request->input('email'))))->first();

        if (! $user) {
            return redirect("/{$locale}/login")->with('success', __($status));
        }

        auth()->login($user);
        $request->session()->regenerate();

        toast(__($status), 'success');

        $dest = self::sanitizeRedirectPath($request->input('redirect'), "/{$locale}");

        if (! $user->hasCompleteProfile()) {
            $dest = self::profileCompletionTarget($locale, $dest);
        }

        return redirect()->to($dest);
    }

    public function showResendVerification(string $locale)
    {
        return view('auth.resend-verification', compact('locale'));
    }

    public function resendVerification(EmailRequest $request, string $locale)
    {
        if (! Settings::get('smtp_enabled', false)) {
            return back()->withErrors([
                'email' => __('auth.resend_disabled'),
            ]);
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (! $user || $user->verified) {
            return back()->with('success', __('auth.resend_sent'));
        }

        try {
            $user->verification_token = Str::random(64);
            $user->verification_token_sent_at = now();
            $user->save();
        } catch (\Throwable $e) {
            AppLog::error(
                'Verification token rotation failed',
                'User ID: '.$user->id.' | '.$e->getMessage(),
                'auth.verification'
            );

            return back()->withInput()->withErrors([
                'email' => __('auth.update_failed'),
            ]);
        }

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
                'email' => __('auth.resend_failed'),
            ]);
        }

        return back()->with('success', __('auth.resend_sent'));
    }

    private function markPendingVerification(Request $request, User $user, string $locale): void
    {
        $request->session()->put('verify_email', $user->email);
        $request->session()->put('verify_autologin', true);
        $request->session()->put(
            'verify_intended',
            self::sanitizeRedirectPath($request->input('redirect'), "/{$locale}")
        );
    }

    public function showVerifyNotice(Request $request, string $locale)
    {
        $email = (string) $request->session()->get('verify_email');

        return view('auth.verify-email', [
            'locale' => $locale,
            'email' => $email !== '' ? $email : null,
            'maskedEmail' => $this->maskEmail($email),
            'smtpEnabled' => (bool) Settings::get('smtp_enabled', false),
        ]);
    }

    public function showVerified(string $locale)
    {
        if (Auth::check()) {
            return redirect()->to("/{$locale}");
        }

        return view('auth.verified', compact('locale'));
    }

    private function maskEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return null;
        }

        [$name, $domain] = explode('@', $email, 2);

        if ($name === '') {
            return null;
        }

        return mb_substr($name, 0, 1).str_repeat('•', max(3, mb_strlen($name) - 1)).'@'.$domain;
    }
}
