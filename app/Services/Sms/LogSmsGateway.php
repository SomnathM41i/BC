<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Development stand-in: writes the message to the log instead of sending it.
 *
 * It refuses to run in production, so payment tracking stays closed there
 * until a real provider is bound in AppServiceProvider.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('No SMS provider is configured.');
        }

        Log::info('SMS not sent (log gateway).', ['mobile' => $mobile, 'message' => $message]);
    }
}
