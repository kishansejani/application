<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use App\Services\Sms\SmsException;
use Illuminate\Support\Facades\Http;

/**
 * MSG91 Flow API (works with a DLT-approved OTP template).
 * https://docs.msg91.com/reference/send-sms
 * The template must contain a variable such as ##otp## (configure the name with MSG91_OTP_VARIABLE).
 */
class Msg91Driver implements SmsDriver
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $text, ?string $otp = null): void
    {
        $key = $this->config['auth_key'] ?? null;
        $template = $this->config['template_id'] ?? null;
        if (!$key || !$template) {
            throw new SmsException('MSG91 is not configured (MSG91_AUTH_KEY, MSG91_TEMPLATE_ID).');
        }

        $recipient = ['mobiles' => $to];
        $recipient[$this->config['otp_variable'] ?? 'otp'] = $otp ?? $text;

        $res = Http::withHeaders(['authkey' => $key, 'accept' => 'application/json'])
            ->timeout((int) config('sms.timeout', 10))
            ->post($this->config['url'] ?? 'https://control.msg91.com/api/v5/flow', [
                'template_id' => $template,
                'short_url' => '0',
                'recipients' => [$recipient],
            ]);

        if ($res->failed() || strtolower((string) $res->json('type')) === 'error') {
            throw new SmsException('MSG91 error ' . $res->status() . ': ' . ($res->json('message') ?? $res->body()));
        }
    }
}
