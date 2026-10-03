<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use App\Services\Sms\SmsException;
use Illuminate\Support\Facades\Http;

/** https://www.twilio.com/docs/sms/api/message-resource#create-a-message-resource */
class TwilioDriver implements SmsDriver
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $text, ?string $otp = null): void
    {
        $sid = $this->config['sid'] ?? null;
        $token = $this->config['token'] ?? null;
        $from = $this->config['from'] ?? null;
        if (!$sid || !$token || !$from) {
            throw new SmsException('Twilio is not configured (TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM).');
        }

        $payload = ['To' => '+' . $to, 'Body' => $text];
        $payload[str_starts_with($from, 'MG') ? 'MessagingServiceSid' : 'From'] = $from;

        $res = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->timeout((int) config('sms.timeout', 10))
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", $payload);

        if ($res->failed()) {
            throw new SmsException('Twilio error ' . $res->status() . ': ' . ($res->json('message') ?? $res->body()));
        }
    }
}
