<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use App\Services\Sms\SmsException;
use Illuminate\Support\Facades\Http;

/**
 * Fast2SMS bulkV2 API (India only).
 * https://docs.fast2sms.com/
 *   route=otp : "Your OTP: 1234" fixed text, no DLT registration needed
 *   route=dlt : your own DLT template (FAST2SMS_TEMPLATE_ID + SMS_SENDER_ID)
 *   route=q   : quick transactional free text
 */
class Fast2SmsDriver implements SmsDriver
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $text, ?string $otp = null): void
    {
        $key = $this->config['api_key'] ?? null;
        if (!$key) {
            throw new SmsException('Fast2SMS is not configured (FAST2SMS_API_KEY).');
        }

        $route = $this->config['route'] ?? 'otp';
        $number = substr($to, -10); // Fast2SMS expects 10-digit Indian numbers
        $payload = ['route' => $route, 'numbers' => $number];

        if ($route === 'otp') {
            $payload['variables_values'] = $otp ?? $text;
        } elseif ($route === 'dlt') {
            $payload['sender_id'] = config('sms.sender_id');
            $payload['message'] = $this->config['template_id'] ?? '';
            $payload['variables_values'] = $otp ?? '';
        } else {
            $payload['message'] = $text;
            $payload['language'] = 'english';
        }

        $res = Http::withHeaders(['authorization' => $key, 'accept' => 'application/json'])
            ->timeout((int) config('sms.timeout', 10))
            ->asForm()
            ->post($this->config['url'] ?? 'https://www.fast2sms.com/dev/bulkV2', $payload);

        if ($res->failed() || $res->json('return') !== true) {
            $msg = $res->json('message');
            throw new SmsException('Fast2SMS error ' . $res->status() . ': ' . (is_array($msg) ? implode(' ', $msg) : ($msg ?? $res->body())));
        }
    }
}
