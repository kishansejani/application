<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test {email : Address that should receive the test e-mail}';

    protected $description = 'Send a test e-mail to check the MAIL_* settings (SMTP, Gmail App Password, ...)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("\"{$email}\" is not a valid e-mail address.");
            return self::INVALID;
        }

        $mailer = (string) config('mail.default');
        $cfg = (array) config("mail.mailers.{$mailer}", []);
        $details = array_filter([
            'Mailer' => $mailer,
            'Host' => $cfg['host'] ?? null,
            'Port' => $cfg['port'] ?? null,
            'Scheme' => $cfg['scheme'] ?? null,
            'Username' => $cfg['username'] ?? null,
            'From' => trim(config('mail.from.name') . ' <' . config('mail.from.address') . '>'),
            'App URL' => config('app.url'),
            'Sent at' => now()->toDateTimeString(),
        ], fn ($v) => $v !== null && $v !== '');

        $this->table(['Setting', 'Value'], collect($details)->map(fn ($v, $k) => [$k, $v])->values()->all());

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn("MAIL_MAILER={$mailer}: the e-mail is written to " . ($mailer === 'log' ? 'storage/logs/laravel.log' : 'memory') . ' and will NOT reach an inbox.');
            $this->line('  Set MAIL_MAILER=smtp and the MAIL_* values in .env (see .env.example), then run: php artisan config:clear');
        }

        try {
            Mail::to($email)->send(new TestMail($details));
        } catch (Throwable $e) {
            $this->error('Sending failed: ' . get_class($e));
            $this->line('  ' . $e->getMessage());
            $this->newLine();
            $this->line('Hints:');
            $this->line('  - Gmail: turn on 2-Step Verification, create an App Password (myaccount.google.com/apppasswords)');
            $this->line('    and use it as MAIL_PASSWORD (16 letters, no spaces). Your normal password will be rejected.');
            $this->line('  - Port 587 -> MAIL_SCHEME=smtp (STARTTLS) ; port 465 -> MAIL_SCHEME=smtps. "tls"/"ssl" are not valid in Laravel 12.');
            $this->line('  - MAIL_FROM_ADDRESS should be the same account as MAIL_USERNAME for Gmail.');
            $this->line('  - After editing .env run: php artisan config:clear');
            return self::FAILURE;
        }

        $this->info("Test e-mail sent to {$email} via \"{$mailer}\".");
        if (!in_array($mailer, ['log', 'array'], true)) {
            $this->line('  Check the inbox (and the spam folder) in a minute.');
        }
        return self::SUCCESS;
    }
}
