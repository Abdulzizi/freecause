<?php

namespace App\Mail;

use App\Support\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyAccountMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $locale;


    /**
     * Create a new message instance.
     */
    public function __construct($user, $locale)
    {
        $this->user = $user;
        $this->locale = $locale;
    }

    // /**
    //  * Get the message envelope.
    //  */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Verify Account Mail',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }

    public function build()
    {
        $subject     = Settings::get('email_verify_subject', '', 'global') ?: 'Verify your account';
        $greeting    = Settings::get('email_verify_greeting', '', 'global') ?: 'Welcome to FreeCause';
        $buttonText  = Settings::get('email_verify_button_text', '', 'global') ?: 'Verify My Account';
        $footer      = Settings::get('email_verify_footer', '', 'global') ?: 'FreeCause – Online Petition Platform';

        return $this->subject($subject)
            ->view('emails.verify-account')
            ->with([
                'user'       => $this->user,
                'locale'     => $this->locale,
                'greeting'   => $greeting,
                'buttonText' => $buttonText,
                'footer'     => $footer,
            ]);
    }
}
