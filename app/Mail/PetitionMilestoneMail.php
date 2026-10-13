<?php

namespace App\Mail;

use App\Models\Petition;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PetitionMilestoneMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Petition $petition,
        public User $owner,
        public int $milestone
    ) {}

    public function build()
    {
        $locale = Locale::toShort($this->owner->locale);
        $translation = $this->petition->translationOrFallback($locale, default_locale());

        $petitionUrl = $translation
            ? route('petition.show', [
                'locale' => $translation->locale,
                'slug' => $translation->slug,
                'id' => $this->petition->id,
            ])
            : lroute('home', ['locale' => $locale]);

        $token = $this->owner->ensureUnsubscribeToken();

        return $this->subject('Your petition just reached '.number_format($this->milestone).' signatures')
            ->view('emails.petition-milestone')
            ->with([
                'firstName' => $this->owner->first_name ?: $this->owner->name,
                'petitionTitle' => $translation?->title ?? 'your petition',
                'petitionUrl' => $petitionUrl,
                'milestone' => $this->milestone,
                'unsubscribeUrl' => lroute('preferences.unsubscribe', ['locale' => $locale, 'token' => $token]),
                'preferencesUrl' => lroute('preferences.show', ['locale' => $locale]),
            ]);
    }
}
