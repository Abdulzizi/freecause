<?php

namespace App\Mail;

use App\Models\Petition;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PetitionDigestMail extends Mailable implements ShouldQueue
{
    use GatedByMailQuota, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $petitions
    ) {}

    public function build()
    {
        $locale = Locale::toShort($this->user->locale ?: default_locale());
        $defaultLocale = default_locale();
        $firstName = $this->user->first_name ?: $this->user->name;
        $token = $this->user->ensureUnsubscribeToken();

        $items = $this->petitions->take(5)->map(function (Petition $petition) use ($locale, $defaultLocale) {
            $translation = $petition->translationOrFallback($locale, $defaultLocale);

            return [
                'id' => $petition->id,
                'title' => $translation?->title,
                'slug' => $translation?->slug,
                'locale' => $translation?->locale ?? $locale,
                'signature_count' => (int) $petition->signature_count,
            ];
        })->values();

        return $this->subject(trans('emails.petition_digest.subject', [], $locale))
            ->view('emails.petition-digest')
            ->with([
                'firstName' => $firstName,
                'items' => $items,
                'locale' => $locale,
                'emailLocale' => $locale,
                'preheader' => trans('emails.petition_digest.preheader', [], $locale),
                'eyebrow' => trans('emails.petition_digest.eyebrow', [], $locale),
                'heading' => trans('emails.petition_digest.heading', [], $locale),
                'greeting' => trans('emails.petition_digest.greeting', ['name' => $firstName], $locale),
                'intro' => trans('emails.petition_digest.intro', [], $locale),
                'signaturesLabel' => trans('emails.petition_digest.signatures', [], $locale),
                'actionLabel' => trans('emails.petition_digest.action', [], $locale),
                'footer' => trans('emails.petition_digest.footer', [], $locale),
                'unsubscribeLabel' => trans('emails.petition_digest.unsubscribe', [], $locale),
                'manageLabel' => trans('preferences.manage_preferences', [], $locale),
                'unsubscribeUrl' => lroute('preferences.unsubscribe', ['locale' => $locale, 'token' => $token]),
                'preferencesUrl' => lroute('preferences.show', ['locale' => $locale]),
            ]);
    }
}
