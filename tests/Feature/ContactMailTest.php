<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use Tests\TestCase;

class ContactMailTest extends TestCase
{
    public function test_contact_email_renders_submitted_message(): void
    {
        $html = (new ContactMail(
            'Test Sender',
            'sender@example.com',
            'Please contact me about my petition.',
        ))->render();

        $this->assertStringContainsString('Test Sender', $html);
        $this->assertStringContainsString('sender@example.com', $html);
        $this->assertStringContainsString('Please contact me about my petition.', $html);
    }
}
