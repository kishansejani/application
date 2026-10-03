<?php

namespace App\Services\Sms;

use App\Services\Sms\Drivers\Fast2SmsDriver;
use App\Services\Sms\Drivers\LogDriver;
use App\Services\Sms\Drivers\Msg91Driver;
use App\Services\Sms\Drivers\TwilioDriver;
use App\Services\Sms\Drivers\TwoFactorDriver;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class SmsManager
{
    public function driverName(): string
    {
        return strtolower((string) config('sms.driver', 'log'));
    }

    /** True when messages actually leave the server. */
    public function isLive(): bool
    {
        return $this->driverName() !== 'log';
    }

    public function driver(?string $name = null): SmsDriver
    {
        $name = strtolower($name ?? $this->driverName());
        $cfg = (array) config("sms.drivers.$name", []);

        return match ($name) {
            'log' => new LogDriver(),
            'twilio' => new TwilioDriver($cfg),
            'msg91' => new Msg91Driver($cfg),
            'fast2sms' => new Fast2SmsDriver($cfg),
            '2factor', 'twofactor' => new TwoFactorDriver($cfg),
            default => throw new InvalidArgumentException("Unsupported SMS driver [$name]."),
        };
    }

    /** "9876543210" -> "919876543210" (already-prefixed numbers are left alone). */
    public function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        $cc = (string) config('sms.country_code', '91');
        if (strlen($digits) === 10) {
            return $cc . $digits;
        }
        return ltrim($digits, '0');
    }

    /** Send an OTP. Any gateway failure is logged and re-thrown as SmsException. */
    public function sendOtp(string $phone, string $code, int $minutes): void
    {
        $text = strtr((string) config('sms.otp_message'), [':code' => $code, ':minutes' => $minutes]);
        $this->send($phone, $text, $code);
    }

    public function send(string $phone, string $text, ?string $otp = null): void
    {
        $to = $this->normalize($phone);
        try {
            $this->driver()->send($to, $text, $otp);
        } catch (Throwable $e) {
            Log::error('[SMS] delivery failed', [
                'driver' => $this->driverName(),
                'to' => substr($to, 0, -4) . '****',
                'error' => $e->getMessage(),
            ]);
            throw $e instanceof SmsException ? $e : new SmsException($e->getMessage(), 0, $e);
        }
    }
}
