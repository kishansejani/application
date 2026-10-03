<?php

$smsDriver = env('SMS_DRIVER', 'log');

return [

    // Number of digits. The verification screens render one box per digit.
    'length' => max(4, min(8, (int) env('OTP_LENGTH', 4))),

    // Minutes a code stays valid.
    'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 5),

    // Wrong guesses allowed before the code is thrown away.
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),

    // Seconds the user must wait before requesting another code.
    'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 30),

    // Codes that may be sent to one phone / email per hour.
    'max_per_hour' => (int) env('OTP_MAX_PER_HOUR', 5),

    // Demo mode: the fixed code below is used and shown as a hint on screen and in API
    // responses. Defaults to ON only while SMS_DRIVER=log. NEVER enable it in production.
    'demo_mode' => (bool) env('OTP_DEMO_MODE', $smsDriver === 'log'),

    'demo_code' => env('OTP_DEMO_CODE', '1234'),
];
