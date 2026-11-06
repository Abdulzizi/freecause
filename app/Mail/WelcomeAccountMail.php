<?php

namespace App\Mail;

use App\Support\MailText;
use Illuminate\Mail\Mailable;

class WelcomeAccountMail extends Mailable
{
    public function __construct(public string $emailLocale)
    {
        $this->locale($emailLocale);
    }

    public function build(): static
    {
        return $this->locale($this->emailLocale)
            ->subject(MailText::get('welcome_subject', [], $this->emailLocale))
            ->view('emails.welcome-account');
    }
}
