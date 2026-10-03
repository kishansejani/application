<?php

namespace App\Services\Sms;

interface SmsDriver
{
    /**
     * Deliver a message.
     *
     * @param  string       $to    Full international number without "+" (e.g. 919876543210)
     * @param  string       $text  Rendered message text
     * @param  string|null  $otp   The bare code, for gateways that fill their own OTP template
     *
     * @throws SmsException
     */
    public function send(string $to, string $text, ?string $otp = null): void;
}
