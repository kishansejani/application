<?php

/*
|--------------------------------------------------------------------------
| SMS gateway
|--------------------------------------------------------------------------
|
| Used to deliver one-time passwords (login, registration, password reset).
| "log" writes every message to storage/logs/laravel.log and is meant for
| local development only - nothing reaches a phone. Pick one of the real
| drivers below and fill in its credentials in .env to send real SMS.
|
| Supported: "log", "twilio", "msg91", "fast2sms", "2factor"
|
*/

return [

    'driver' => env('SMS_DRIVER', 'log'),

    // Default country calling code (without "+") prepended to 10-digit numbers.
    'country_code' => env('SMS_COUNTRY_CODE', '91'),

    // 6-character DLT sender id / header (India) - used by drivers that support it.
    'sender_id' => env('SMS_SENDER_ID', 'FRSHEX'),

    // HTTP timeout (seconds) for gateway calls.
    'timeout' => (int) env('SMS_TIMEOUT', 10),

    // Text used by drivers that send a free-form message (log, twilio, fast2sms "q" route).
    // :code and :minutes are replaced. DLT-registered templates must match this text exactly.
    'otp_message' => env('SMS_OTP_MESSAGE', ':code is your Fresh Express verification code. It is valid for :minutes minutes. Do not share it with anyone.'),

    'drivers' => [

        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'), // e.g. +15005550006 or a Messaging Service SID (MG...)
        ],

        'msg91' => [
            'auth_key' => env('MSG91_AUTH_KEY'),
            'template_id' => env('MSG91_TEMPLATE_ID'), // Flow / OTP template id from the MSG91 panel
            'otp_variable' => env('MSG91_OTP_VARIABLE', 'otp'), // the ##var## name used in the template
            'url' => env('MSG91_URL', 'https://control.msg91.com/api/v5/flow'),
        ],

        'fast2sms' => [
            'api_key' => env('FAST2SMS_API_KEY'),
            'route' => env('FAST2SMS_ROUTE', 'otp'), // "otp" (fixed text, no DLT needed) | "dlt" | "q"
            'template_id' => env('FAST2SMS_TEMPLATE_ID'), // only for route=dlt (message id)
            'url' => env('FAST2SMS_URL', 'https://www.fast2sms.com/dev/bulkV2'),
        ],

        '2factor' => [
            'api_key' => env('TWOFACTOR_API_KEY'),
            'template' => env('TWOFACTOR_TEMPLATE'), // optional OTP template name
            'url' => env('TWOFACTOR_URL', 'https://2factor.in/API/V1'),
        ],

    ],
];
