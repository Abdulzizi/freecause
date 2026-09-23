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

    public string $body;

    public ?string $subjectLine;

    public function __construct(string $senderName, string $senderEmail, string $message, ?string $subjectLine = null)
    {
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
        $this->body = $message;
        $this->subjectLine = $subjectLine;
    }

    public function build()
    {
        $subject = $this->subjectLine ?: 'Contact form: '.$this->senderName;

        return $this->subject($subject)
            ->replyTo($this->senderEmail, $this->senderName)
            ->view('emails.contact');
    }
}
