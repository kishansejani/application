<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Sent by `php artisan mail:test {email}` to check the SMTP settings. */
class TestMail extends Mailable
{
    use Queueable;

    public function __construct(public array $details = [])
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('auth_ui.mail_test_subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.test');
    }
}
