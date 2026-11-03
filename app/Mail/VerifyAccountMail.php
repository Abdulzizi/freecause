<?php

namespace App\Mail;

use App\Support\Settings;
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
        $this->locale = $locale;
    }

    public function build()
    {
        $subject = Settings::get('email_verify_subject', '', 'global') ?: 'Verify your account';
        $greeting = Settings::get('email_verify_greeting', '', 'global') ?: 'Welcome to FreeCause';
        $buttonText = Settings::get('email_verify_button_text', '', 'global') ?: 'Verify My Account';
        $footer = Settings::get('email_verify_footer', '', 'global') ?: 'FreeCause – Online Petition Platform';

        return $this->subject($subject)
            ->view('emails.verify-account')
            ->with([
                'firstName' => $this->firstName,
                'locale' => $this->locale,
                'token' => $this->verificationToken,
                'greeting' => $greeting,
                'buttonText' => $buttonText,
                'footer' => $footer,
            ]);
    }
}
