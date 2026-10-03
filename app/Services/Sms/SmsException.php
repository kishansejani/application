<?php

namespace App\Services\Sms;

use RuntimeException;

/** Thrown when an SMS gateway rejects a message or cannot be reached. */
class SmsException extends RuntimeException
{
}
