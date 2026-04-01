<?php

namespace App\Mail;

use App\Models\User;
use App\Support\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyAccountMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $firstName;
    public string $verificationToken;
    public string $locale;

    /**
     * Accept primitives instead of the User model so the job does not crash
     * if the user is deleted before the queue worker processes it.
     *
     * @param User|object $user
     */
    public function __construct($user, string $locale)
    {
        $this->firstName         = $user->first_name ?? '';
        $this->verificationToken = $user->verification_token ?? '';
        $this->locale            = $locale;
    }

    public function build()
    {
        if (empty($this->verificationToken)) {
            $this->delete();
            return $this;
        }

        $subject    = Settings::get('email_verify_subject', '', 'global') ?: 'Verify your account';
        $greeting   = Settings::get('email_verify_greeting', '', 'global') ?: 'Welcome to FreeCause';
        $buttonText = Settings::get('email_verify_button_text', '', 'global') ?: 'Verify My Account';
        $footer     = Settings::get('email_verify_footer', '', 'global') ?: 'FreeCause – Online Petition Platform';

        return $this->subject($subject)
            ->view('emails.verify-account')
            ->with([
                'firstName'  => $this->firstName,
                'locale'     => $this->locale,
                'token'      => $this->verificationToken,
                'greeting'   => $greeting,
                'buttonText' => $buttonText,
                'footer'     => $footer,
            ]);
    }
}
