<?php

namespace App\Services\Otp;

use RuntimeException;
use Throwable;

/**
 * A user-facing OTP problem. getMessage() is already translated and safe to show.
 */
class OtpException extends RuntimeException
{
    public const COOLDOWN = 'cooldown';
    public const HOURLY_LIMIT = 'hourly_limit';
    public const DELIVERY_FAILED = 'delivery_failed';
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const TOO_MANY_ATTEMPTS = 'too_many_attempts';
    public const NOT_FOUND = 'not_found';

    public function __construct(
        public readonly string $reason,
        string $message,
        public readonly int $retryAfter = 0,
        public readonly ?int $attemptsLeft = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /** Suggested HTTP status for JSON responses. */
    public function status(): int
    {
        return match ($this->reason) {
            self::COOLDOWN, self::HOURLY_LIMIT => 429,
            self::DELIVERY_FAILED => 503,
            default => 422,
        };
    }

    /** Should the client send the user back to request a new code? */
    public function codeIsGone(): bool
    {
        return in_array($this->reason, [self::EXPIRED, self::TOO_MANY_ATTEMPTS, self::NOT_FOUND], true);
    }

    public function toArray(): array
    {
        return array_filter([
            'reason' => $this->reason,
            'retry_after' => $this->retryAfter ?: null,
            'attempts_left' => $this->attemptsLeft,
        ], fn ($v) => $v !== null);
    }
}
