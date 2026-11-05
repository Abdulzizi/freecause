<?php

namespace App\Mail;

use App\Support\Locale;
use App\Support\MailText;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyAccountMail extends Mailable implements ShouldQueue
{
    use GatedByMailQuota, Queueable, SerializesModels;

    public string $firstName;

    public string $verificationToken;

    public $locale;

    public function __construct($user, string $locale)
    {
        $this->firstName = $user->first_name ?? '';
        $this->verificationToken = $user->verification_token ?? '';
        $this->locale = Locale::toShort(! empty($user->preferred_language_confirmed_at) ? $user->locale : $locale);
    }

    public function build()
    {
        return $this->subject(MailText::get('verify_subject', [], $this->locale))
            ->view('emails.verify-account')
            ->with(['firstName' => $this->firstName, 'locale' => $this->locale,
                'emailLocale' => $this->locale, 'token' => $this->verificationToken]);
    }
}
