<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Branded password-reset e-mail: big one-time code + a signed one-click reset link. */
class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public int $minutes,
        public ?string $resetUrl = null,
        public int $linkMinutes = 60,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('auth_ui.mail_subject', ['code' => $this->code]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-reset');
    }
}
