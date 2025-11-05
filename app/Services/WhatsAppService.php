<?php

namespace App\Services;

/**
 * Adapter for WhatsApp Business API.
 *
 * NOTE: Scaffold: implement provider-specific code (Twilio, Meta) and store credentials in .env.
 */
class WhatsAppService
{
    public function __construct()
    {
        // TODO: inject http client and credentials
    }

    public function sendMessage(string $to, string $message): array
    {
        // TODO: implement sending via provider
        return ['status' => 'not_implemented'];
    }
}
