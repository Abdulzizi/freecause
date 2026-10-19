<?php

namespace App\Services;

use App\Mail\VerifyAccountMail;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\AppLog;
use App\Support\Settings;
use App\Support\Spam;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PetitionSigning
{
    /**
     * Single source of truth for guest sign-form validation rules.
     * The guest form posts the agreement checkboxes as 'on'/'1'.
     *
     * @return array<string,array<int,string>>
     */
    public static function guestRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:190'],
            'comment' => ['nullable', 'string', 'max:500'],
            'agree1' => ['required', 'in:on,1'],
            'agree2' => ['required', 'in:on,1'],
            'agree3' => ['required', 'in:on,1'],
        ];
    }

    /**
     * Single source of truth for authenticated sign-form validation rules.
     * The authed form posts the agreement checkboxes as 'agree'.
     *
     * @return array<string,array<int,string>>
     */
    public static function authedRules(): array
    {
        return [
            'comment' => ['nullable', 'string', 'max:500'],
            'agree1' => ['required', 'in:agree'],
            'agree2' => ['required', 'in:agree'],
            'agree3' => ['required', 'in:agree'],
        ];
    }

    public static function signAsGuest(Request $request, Petition $petition, string $locale): RedirectResponse
    {
        $tr = $petition->translationOrFallback($locale, default_locale());

        $data = $request->validate(self::guestRules());

        if (! Spam::verifyTurnstile($request->input('cf-turnstile-response'))) {
            toast(__('sign.captcha_failed'), 'error');

            return back()->withInput();
        }

        $spamText = implode(' ', [
            $data['name'] ?? '',
            $data['comment'] ?? '',
        ]);

        if (Spam::isSpam($spamText)) {
            Spam::log('signature', json_encode($data));

            toast('Suspicious activity detected.', 'error');

            return back()->withInput();
        }

        if (Spam::rateLimit('petition')) {
            Spam::log('petition', 'Rate limit exceeded');
            Spam::banCurrentIp('Too many petition attempts');

            toast('Too many petition attempts.', 'error');

            return back()->withInput();
        }

        $email = strtolower(trim($data['email']));

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            return back()->withErrors(['email' => __('sign.verify_first')]);
        }

        $alreadySigned = Signature::where('petition_id', $petition->id)
            ->where('email', $email)
            ->exists();

        if ($alreadySigned) {
            return back()
                ->withErrors(['email' => 'You already signed this petition.'])
                ->withInput($request->except('password'));
        }

        try {
            $counted = false;
            $previousCount = null;

            $response = DB::transaction(function () use ($data, $email, $locale, $petition, $tr, &$counted, &$previousCount) {
                $userLevel = UserLevel::where('name', 'user')->first();
                $needsVerification = (bool) Settings::get('require_email_verification', false, 'global');

                $user = User::create([
                    'name' => $data['name'],
                    'first_name' => $data['name'],
                    'last_name' => null,
                    'email' => $email,
                    'locale' => $locale,
                    'ip' => request()->ip(),
                    'level_id' => $userLevel?->id,
                    'verified' => ! $needsVerification,
                    'verification_token' => $needsVerification ? Str::random(64) : null,
                    'verification_token_sent_at' => $needsVerification ? now() : null,
                ]);

                if ($needsVerification && Settings::get('smtp_enabled', false)) {
                    DB::afterCommit(function () use ($user, $locale) {
                        try {
                            Mail::to($user->email)->queue(new VerifyAccountMail($user, $locale));
                        } catch (\Throwable $e) {
                            AppLog::error('Verification email failed', $e->getMessage(), 'petition.sign');
                        }
                    });
                }

                $sig = Signature::firstOrCreate(
                    ['petition_id' => $petition->id, 'email' => $email],
                    [
                        'user_id' => $user->id,
                        'name' => $data['name'],
                        'locale' => $locale,
                        'text' => $data['comment'] ?? 'I support this petition',
                        'confirmed' => $user->verified,
                        'ip_address' => request()->ip(),
                    ]
                );

                if ($sig->wasRecentlyCreated && $sig->confirmed) {
                    $counted = true;
                    $previousCount = PetitionStats::incrementMany([$petition->id => 1])[$petition->id] ?? null;
                }

                AppLog::info(
                    'Petition signed',
                    'Petition ID: '.$petition->id.' | User: '.$email.' | IP: '.request()->ip(),
                    'petition.sign'
                );

                session()->forget("sign.{$petition->id}");
                if ($user->verified) {
                    session()->put('sign_claim', [
                        'email' => $email,
                        'is_new' => true,
                        'name' => $data['name'],
                    ]);
                }

                return redirect()->route('petition.thanks', [
                    'locale' => $tr->locale,
                    'slug' => $tr->slug,
                    'id' => $petition->id,
                ])->with('petition_verified', (bool) $user->verified);
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23000') {
                return back()
                    ->withErrors(['email' => 'You already signed this petition.'])
                    ->withInput($request->except('password'));
            }

            throw $e;
        }

        if ($counted) {
            PetitionStats::notifyMilestoneIfCrossed($petition->id, $previousCount ?? null);
        }

        return $response;
    }

    public static function signAsAuthed(Request $request, string $locale, Petition $petition): RedirectResponse
    {
        $data = $request->validate(self::authedRules());

        $u = auth()->user();

        try {
            $counted = false;
            $previousCount = null;

            $response = DB::transaction(function () use ($data, $locale, $petition, $u, &$counted, &$previousCount) {
                $dup = Signature::where('petition_id', $petition->id)->where(fn ($q) => $q->where('email', $u->email)->orWhere('user_id', $u->id))->exists();
                if ($dup) {
                    $tr = $petition->translationOrFallback($locale);
                    abort_if(! $tr, 404);
                    toast('You already signed this petition.', 'info');

                    return redirect()->route('petition.thanks', ['locale' => $tr->locale, 'slug' => $tr->slug, 'id' => $petition->id])->with('petition_verified', true);
                }
                $sig = Signature::firstOrCreate(
                    ['petition_id' => $petition->id, 'email' => $u->email],
                    [
                        'user_id' => $u->id,
                        'name' => $u->display_name, // frozen snapshot; live display via Signature::display_name
                        'locale' => $locale,
                        'text' => $data['comment'] ?? 'I support this petition',
                        'ip_address' => request()->ip(),
                    ]
                );

                if (! $sig->wasRecentlyCreated) {
                    toast('You already signed this petition.', 'info');
                }

                if ($sig->wasRecentlyCreated) {
                    $counted = true;
                    $previousCount = PetitionStats::incrementMany([$petition->id => 1])[$petition->id] ?? null;
                }

                session()->forget("sign.{$petition->id}");

                $tr = $petition->translationOrFallback($locale);

                abort_if(! $tr, 404);

                AppLog::info(
                    'Petition signed',
                    'Petition ID: '.$petition->id.' | User: '.$u->email.' | IP: '.request()->ip(),
                    'petition.sign'
                );

                if ($sig->wasRecentlyCreated) {
                    toast('Thank you for signing!', 'success');
                }

                return redirect()->route('petition.thanks', [
                    'locale' => $tr->locale,
                    'slug' => $tr->slug,
                    'id' => $petition->id,
                ])->with('petition_verified', true);
            });
        } catch (QueryException $e) {

            if (($e->errorInfo[0] ?? null) !== '23000' && ! str_contains($e->getMessage(), 'UNIQUE')) {
                throw $e;
            }

            $sig = Signature::where('petition_id', $petition->id)
                ->where(function ($q) use ($u) {
                    $q->where('email', $u->email)->orWhere('user_id', $u->id);
                })
                ->first();

            if ($sig) {
                toast('You already signed this petition.', 'info');
            } else {
                throw $e;
            }

            $tr = PetitionTranslation::query()
                ->where('petition_id', $petition->id)
                ->where('locale', $locale)
                ->first()
                ?? PetitionTranslation::query()->where('petition_id', $petition->id)->orderBy('id')->first();

            abort_if(! $tr, 404);

            return redirect()->route('petition.thanks', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ])->with('petition_verified', true);
        }

        if ($counted) {
            PetitionStats::notifyMilestoneIfCrossed($petition->id, $previousCount ?? null);
        }

        return $response;
    }
}
