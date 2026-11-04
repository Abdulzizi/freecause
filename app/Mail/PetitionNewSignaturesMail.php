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

class PetitionNewSignaturesMail extends Mailable implements ShouldQueue
{
    use GatedByMailQuota, Queueable, SerializesModels;

    public function __construct(
        public Petition $petition,
        public User $owner,
        public int $count,
        public Collection $signers
    ) {}

    public function build()
    {
        $locale = Locale::toShort($this->owner->locale ?: default_locale());
        $translation = $this->petition->translationOrFallback($locale, default_locale());
        $token = $this->owner->ensureUnsubscribeToken();

        $petitionUrl = $translation
            ? route('petition.show', [
                'locale' => $translation->locale,
                'slug' => $translation->slug,
                'id' => $this->petition->id,
            ])
            : lroute('home', ['locale' => $locale]);

        return $this->subject(trans('emails.petition_new_signatures.subject', ['count' => $this->count], $locale))
            ->view('emails.petition-new-signatures')
            ->with([
                'firstName' => $this->owner->first_name ?: $this->owner->name,
                'petitionTitle' => $translation?->title ?? 'your petition',
                'petitionUrl' => $petitionUrl,
                'count' => $this->count,
                'signers' => $this->signers,
                'heading' => trans('emails.petition_new_signatures.heading', ['count' => $this->count], $locale),
                'intro' => trans('emails.petition_new_signatures.intro', ['title' => $translation?->title ?? '', 'count' => $this->count], $locale),
                'guestNote' => trans('emails.petition_new_signatures.guest_note', [], $locale),
                'footer' => trans('emails.petition_new_signatures.footer', [], $locale),
                'unsubscribeLabel' => trans('emails.petition_new_signatures.unsubscribe', [], $locale),
                'manageLabel' => trans('preferences.manage_preferences', [], $locale),
                'unsubscribeUrl' => lroute('preferences.unsubscribe', ['locale' => $locale, 'token' => $token]),
                'preferencesUrl' => lroute('preferences.show', ['locale' => $locale]),
            ]);
    }
}
