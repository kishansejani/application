<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Services\Otp\OtpException;
use App\Services\Sms\SmsManager;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * One-time passwords for login, registration and password reset.
 *
 *  - codes are random digits, stored only as an HMAC (never in plain text)
 *  - each code expires (otp.expiry_minutes) and dies after otp.max_attempts wrong guesses
 *  - a resend cool-down and an hourly cap per phone / email protect the SMS budget
 *  - codes are scoped to a purpose, so a login code cannot reset a password
 *  - demo mode (only sensible with SMS_DRIVER=log) uses the fixed otp.demo_code
 */
class OtpService
{
    public const LOGIN = 'login';
    public const REGISTER = 'register';
    public const RESET = 'reset';

    public function __construct(private SmsManager $sms)
    {
    }

    // ---------------------------------------------------------------- config

    public function length(): int
    {
        return (int) config('otp.length', 4);
    }

    public function expiryMinutes(): int
    {
        return max(1, (int) config('otp.expiry_minutes', 5));
    }

    public function resendSeconds(): int
    {
        return max(0, (int) config('otp.resend_seconds', 30));
    }

    public function maxAttempts(): int
    {
        return max(1, (int) config('otp.max_attempts', 5));
    }

    /**
     * Demo mode is honoured only while the channel cannot reach a real person:
     * SMS needs SMS_DRIVER=log, e-mail needs MAIL_MAILER=log/array. As soon as a real
     * gateway / SMTP server is configured, random codes are used and never exposed.
     */
    public function isDemo(string $channel = 'sms'): bool
    {
        if (!config('otp.demo_mode', false)) {
            return false;
        }
        if ($channel === 'email') {
            return in_array(config('mail.default'), ['log', 'array'], true);
        }
        return !$this->sms->isLive();
    }

    /** The code to show as a hint - null unless demo mode is on for that channel. */
    public function demoCode(string $channel = 'sms'): ?string
    {
        if (!$this->isDemo($channel)) {
            return null;
        }
        return substr(preg_replace('/\D/', '', (string) config('otp.demo_code', '1234')) . '1234567890', 0, $this->length());
    }

    // ---------------------------------------------------------------- send

    /**
     * Create a code and deliver it.
     *
     * @param  string        $identifier  10-digit phone or email address
     * @param  string        $purpose     login | register | reset
     * @param  string        $channel     sms | email
     * @param  Closure|null  $deliver     fn(string $code, int $minutes) for custom channels (e-mail)
     * @return array{expires_at: Carbon, expires_in: int, resend_in: int, length: int, demo_code: ?string}
     *
     * @throws OtpException
     */
    public function send(string $identifier, string $purpose, string $channel = 'sms', ?Closure $deliver = null): array
    {
        $identifier = $this->key($identifier);
        $this->guardLimits($identifier, $purpose, $channel);

        $code = $this->demoCode($channel) ?? $this->randomCode();
        $minutes = $this->expiryMinutes();

        OtpCode::where('identifier', $identifier)->where('purpose', $purpose)->delete();
        $record = OtpCode::create([
            'identifier' => $identifier,
            'channel' => $channel,
            'purpose' => $purpose,
            'code_hash' => $this->hash($identifier, $purpose, $code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($minutes),
        ]);

        try {
            if ($deliver) {
                $deliver($code, $minutes);
            } else {
                $this->sms->sendOtp($identifier, $code, $minutes);
            }
        } catch (Throwable $e) {
            // Let the user retry straight away; a failed delivery should not burn their quota.
            $record->delete();
            RateLimiter::clear($this->cooldownKey($identifier, $purpose));
            Log::error("[OTP] {$channel} delivery failed for {$purpose}", ['error' => $e->getMessage()]);

            $message = __($channel === 'email' ? 'auth_ui.mail_failed' : 'auth_ui.sms_failed');
            if (config('app.debug')) {
                $message .= ' [' . class_basename($e) . ': ' . $e->getMessage() . ']';
            }
            throw new OtpException(OtpException::DELIVERY_FAILED, $message, 0, null, $e);
        }

        return [
            'expires_at' => $record->expires_at,
            'expires_in' => $minutes * 60,
            'resend_in' => $this->resendIn($identifier, $purpose),
            'length' => $this->length(),
            'demo_code' => $this->demoCode($channel),
        ];
    }

    /**
     * Apply the cool-down + hourly cap without sending anything. Used when the account
     * does not exist, so the response looks (and is throttled) exactly like a real send.
     *
     * @throws OtpException
     */
    public function pretendSend(string $identifier, string $purpose, string $channel = 'sms'): array
    {
        $identifier = $this->key($identifier);
        $this->guardLimits($identifier, $purpose, $channel);

        // Store an undeliverable random code so a later verify behaves exactly like a real one
        // ("incorrect code, N attempts left") instead of revealing that nothing was sent.
        OtpCode::where('identifier', $identifier)->where('purpose', $purpose)->delete();
        OtpCode::create([
            'identifier' => $identifier,
            'channel' => $channel,
            'purpose' => $purpose,
            'code_hash' => $this->hash($identifier, $purpose, bin2hex(random_bytes(16))),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($this->expiryMinutes()),
        ]);

        return [
            'expires_at' => now()->addMinutes($this->expiryMinutes()),
            'expires_in' => $this->expiryMinutes() * 60,
            'resend_in' => $this->resendIn($identifier, $purpose),
            'length' => $this->length(),
            'demo_code' => $this->demoCode($channel),
        ];
    }

    /** Seconds until another code may be requested. */
    public function resendIn(string $identifier, string $purpose): int
    {
        $key = $this->cooldownKey($this->key($identifier), $purpose);
        return RateLimiter::tooManyAttempts($key, 1) ? RateLimiter::availableIn($key) : 0;
    }

    // ---------------------------------------------------------------- verify

    /**
     * Check a code. Succeeds once - the code is consumed.
     *
     * @throws OtpException
     */
    public function verify(string $identifier, string $purpose, string $code): bool
    {
        $identifier = $this->key($identifier);
        $code = preg_replace('/\D/', '', $code);

        $record = OtpCode::where('identifier', $identifier)->where('purpose', $purpose)->latest('id')->first();

        if (!$record) {
            throw new OtpException(OtpException::NOT_FOUND, __('auth_ui.otp_not_found'));
        }
        if ($record->expires_at->isPast()) {
            $record->delete();
            throw new OtpException(OtpException::EXPIRED, __('auth_ui.otp_expired'));
        }

        if (hash_equals($record->code_hash, $this->hash($identifier, $purpose, $code))) {
            $record->delete();
            RateLimiter::clear($this->cooldownKey($identifier, $purpose));
            return true;
        }

        $record->increment('attempts');
        $left = $this->maxAttempts() - $record->attempts;
        if ($left <= 0) {
            $record->delete();
            throw new OtpException(OtpException::TOO_MANY_ATTEMPTS, __('auth_ui.otp_too_many_attempts'), 0, 0);
        }

        throw new OtpException(OtpException::INVALID, trans_choice('auth_ui.otp_invalid', $left, ['count' => $left]), 0, $left);
    }

    /** Drop any pending code (e.g. when the user changes number). */
    public function forget(string $identifier, string $purpose): void
    {
        OtpCode::where('identifier', $this->key($identifier))->where('purpose', $purpose)->delete();
    }

    /** House-keeping: remove expired rows. */
    public function prune(): int
    {
        return OtpCode::where('expires_at', '<', now()->subDay())->delete();
    }

    // ---------------------------------------------------------------- internals

    /** Is there an unexpired, unused code for this identifier + purpose? */
    public function hasActiveCode(string $identifier, string $purpose): bool
    {
        return OtpCode::where('identifier', $this->key($identifier))->where('purpose', $purpose)
            ->where('expires_at', '>', now())->exists();
    }

    private function guardLimits(string $identifier, string $purpose, string $channel = 'sms'): void
    {
        $cool = $this->cooldownKey($identifier, $purpose);
        if (RateLimiter::tooManyAttempts($cool, 1)) {
            $s = RateLimiter::availableIn($cool);
            throw new OtpException(OtpException::COOLDOWN, trans_choice('auth_ui.otp_cooldown', $s, ['seconds' => $s]), $s);
        }

        // The hourly cap protects the SMS / mail budget; demo mode sends nothing, so it is skipped there.
        $hour = 'otp:hour:' . sha1($identifier);
        $max = max(1, (int) config('otp.max_per_hour', 5));
        if (!$this->isDemo($channel) && RateLimiter::tooManyAttempts($hour, $max)) {
            $s = RateLimiter::availableIn($hour);
            throw new OtpException(OtpException::HOURLY_LIMIT, __('auth_ui.otp_hourly_limit', ['minutes' => (int) ceil($s / 60)]), $s);
        }

        if ($this->resendSeconds() > 0) {
            RateLimiter::hit($cool, $this->resendSeconds());
        }
        RateLimiter::hit($hour, 3600);
    }

    private function cooldownKey(string $identifier, string $purpose): string
    {
        return 'otp:cool:' . $purpose . ':' . sha1($identifier);
    }

    private function key(string $identifier): string
    {
        $identifier = trim($identifier);
        return str_contains($identifier, '@') ? mb_strtolower($identifier) : preg_replace('/\D/', '', $identifier);
    }

    private function randomCode(): string
    {
        $len = $this->length();
        return str_pad((string) random_int(0, 10 ** $len - 1), $len, '0', STR_PAD_LEFT);
    }

    private function hash(string $identifier, string $purpose, string $code): string
    {
        return hash_hmac('sha256', $purpose . '|' . $identifier . '|' . $code, (string) config('app.key'));
    }
}
