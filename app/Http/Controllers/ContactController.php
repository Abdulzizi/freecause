<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMail;
use App\Support\AppLog;
use App\Support\Settings;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(ContactRequest $request)
    {
        if (! Settings::get('email_contact_enabled', true, 'global')) {
            abort(404);
        }

        $data = $request->validated();

        $adminEmail = Settings::get('email_to_staff', '', 'global') ?: config('mail.from.address');
        $subject = Settings::get('email_contact_subject', '', 'global') ?: null;

        try {
            Mail::to($adminEmail)->queue(new ContactMail($data['name'], $data['email'], $data['text'], $subject));
        } catch (\Exception $e) {
            AppLog::error('Contact form mail failed', $e->getMessage(), 'contacts');

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to send message. Please try again later.'], 500);
            }

            toast('Failed to send message. Please try again later.', 'error');

            return back()
                ->withInput()
                ->withErrors(['email' => 'Failed to send message. Please try again later.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent. We will get back to you soon.',
            ]);
        }

        toast('Your message has been sent. We will get back to you soon.', 'success');

        return back();
    }
}
