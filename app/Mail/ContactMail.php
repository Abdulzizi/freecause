<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public string $senderEmail;
    public string $message;

    public function __construct(string $senderName, string $senderEmail, string $message)
    {
        $this->senderName  = $senderName;
        $this->senderEmail = $senderEmail;
        $this->message     = $message;
    }

    public function build()
    {
        return $this->subject('Contact form: ' . $this->senderName)
            ->replyTo($this->senderEmail, $this->senderName)
            ->view('emails.contact');
    }
}
