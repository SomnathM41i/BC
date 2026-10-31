<?php

namespace App\Contracts;

/**
 * Sends a text message to a 10-digit Indian mobile number.
 *
 * Implementations must throw when the message could not be handed over, so
 * callers never tell a visitor that a code is on its way when it is not.
 */
interface SmsGateway
{
    public function send(string $mobile, string $message): void;
}
