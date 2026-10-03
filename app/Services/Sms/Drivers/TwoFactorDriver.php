<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use App\Services\Sms\SmsException;
use Illuminate\Support\Facades\Http;

/**
 * 2Factor.in OTP API: GET /API/V1/{api_key}/SMS/{phone}/{otp}/{template?}
 * https://2factor.in/API/DOCS/SMS_OTP.html
 */
class TwoFactorDriver implements SmsDriver
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $text, ?string $otp = null): void
    {
        $key = $this->config['api_key'] ?? null;
        if (!$key) {
            throw new SmsException('2Factor is not configured (TWOFACTOR_API_KEY).');
        }
        if ($otp === null) {
            throw new SmsException('2Factor driver only supports OTP messages.');
        }

        $url = rtrim($this->config['url'] ?? 'https://2factor.in/API/V1', '/')
            . '/' . rawurlencode($key) . '/SMS/' . rawurlencode($to) . '/' . rawurlencode($otp);
        if (!empty($this->config['template'])) {
            $url .= '/' . rawurlencode($this->config['template']);
        }

        $res = Http::timeout((int) config('sms.timeout', 10))->get($url);

        if ($res->failed() || strcasecmp((string) $res->json('Status'), 'Success') !== 0) {
            throw new SmsException('2Factor error ' . $res->status() . ': ' . ($res->json('Details') ?? $res->body()));
        }
    }
}
