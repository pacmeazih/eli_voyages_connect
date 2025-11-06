<?php

namespace App\Services;

use App\Models\Dossier;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ContractService
{
    protected Client $client;
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.docuseal.api_url', 'https://api.docuseal.co');
        $this->apiKey = config('services.docuseal.api_key');
        
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'X-Auth-Token' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    /**
     * Generate a contract from a DOCX template with variables
     */
    public function generateFromTemplate(
        string $templatePath,
        array $variables,
        Dossier $dossier
    ): array {
        try {
            $response = $this->client->post('/submissions', [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($templatePath, 'r'),
                        'filename' => basename($templatePath),
                    ],
                    [
                        'name' => 'data',
                        'contents' => json_encode($variables),
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            // Log activity
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties(['contract_id' => $result['id'] ?? null])
                ->log('Contract generated from template');

            return $result;
        } catch (\Exception $e) {
            Log::error('DocuSeal contract generation failed', [
                'error' => $e->getMessage(),
                'dossier_id' => $dossier->id,
            ]);
            throw $e;
        }
    }

    /**
     * Create a submission request for signing
     */
    public function createSignatureRequest(
        int $templateId,
        array $submitters,
        Dossier $dossier
    ): array {
        try {
            $response = $this->client->post('/submissions', [
                'json' => [
                    'template_id' => $templateId,
                    'send_email' => true,
                    'submitters' => $submitters,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            // Log activity
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties([
                    'submission_id' => $result['id'] ?? null,
                    'submitters' => count($submitters),
                ])
                ->log('Signature request sent');

            return $result;
        } catch (\Exception $e) {
            Log::error('DocuSeal signature request failed', [
                'error' => $e->getMessage(),
                'dossier_id' => $dossier->id,
            ]);
            throw $e;
        }
    }

    /**
     * Get submission status
     */
    public function getSubmissionStatus(string $submissionId): array
    {
        try {
            $response = $this->client->get("/submissions/{$submissionId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('DocuSeal status check failed', [
                'error' => $e->getMessage(),
                'submission_id' => $submissionId,
            ]);
            throw $e;
        }
    }

    /**
     * List all templates
     */
    public function listTemplates(): array
    {
        try {
            $response = $this->client->get('/templates');
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('DocuSeal templates list failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process webhook from DocuSeal
     */
    public function processWebhook(array $payload): void
    {
        $eventType = $payload['event_type'] ?? null;
        $submissionId = $payload['submission_id'] ?? null;

        Log::info('DocuSeal webhook received', [
            'event_type' => $eventType,
            'submission_id' => $submissionId,
        ]);

        switch ($eventType) {
            case 'form.completed':
                $this->handleFormCompleted($payload);
                break;
            case 'form.viewed':
                $this->handleFormViewed($payload);
                break;
            case 'form.started':
                $this->handleFormStarted($payload);
                break;
            default:
                Log::info('Unhandled DocuSeal webhook event', ['event' => $eventType]);
        }
    }

    /**
     * Handle form completed event
     */
    protected function handleFormCompleted(array $payload): void
    {
        // TODO: Update dossier status
        // TODO: Send notifications
        // TODO: Store signed document
        
        activity()
            ->withProperties($payload)
            ->log('Contract signed and completed');
    }

    /**
     * Handle form viewed event
     */
    protected function handleFormViewed(array $payload): void
    {
        activity()
            ->withProperties($payload)
            ->log('Contract viewed by signer');
    }

    /**
     * Handle form started event
     */
    protected function handleFormStarted(array $payload): void
    {
        activity()
            ->withProperties($payload)
            ->log('Contract signing started');
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        // TODO: Implement signature validation based on DocuSeal documentation
        // For now, return true in development
        if (config('app.env') === 'local') {
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->apiKey);
        return hash_equals($expectedSignature, $signature);
    }
}
