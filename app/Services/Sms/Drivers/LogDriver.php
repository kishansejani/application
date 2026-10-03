<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDriver;
use Illuminate\Support\Facades\Log;

/** Development driver: writes the message to storage/logs/laravel.log instead of sending it. */
class LogDriver implements SmsDriver
{
    public function send(string $to, string $text, ?string $otp = null): void
    {
        Log::info("[SMS:log] To +{$to}: {$text}");
    }
}
