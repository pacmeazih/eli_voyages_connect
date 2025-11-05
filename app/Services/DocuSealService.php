<?php

namespace App\Services;

/**
 * Adapter/service for DocuSeal e-sign API.
 *
 * NOTE: This is a scaffold. Add API credentials in .env and implement HTTP calls (Guzzle).
 */
class DocuSealService
{
    public function __construct()
    {
        // TODO: inject HTTP client, base_url, api_key from config
    }

    public function createEnvelope(array $payload): array
    {
        // TODO: call DocuSeal create envelope endpoint
        return ['status' => 'not_implemented'];
    }

    public function getEnvelopeStatus(string $envelopeId): array
    {
        // TODO: call status endpoint
        return ['status' => 'not_implemented'];
    }

    public function handleWebhook(array $data): void
    {
        // TODO: implement webhook handling logic
    }
}
