<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;

/**
 * DocuSeal API Service for e-signature integration
 * 
 * This service handles all interactions with DocuSeal API for
 * creating submissions, checking status, and downloading signed documents.
 */
class DocuSealService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.docuseal.api_key');
        $this->apiUrl = config('services.docuseal.api_url', 'https://api.docuseal.com');
    }

    /**
     * Create a submission (send contract for signature)
     * 
     * @param int $templateId DocuSeal template ID
     * @param array $submitters Array of submitters with role, name, email, etc.
     * @param array $options Additional options (expire_at, redirect_url, etc.)
     * @return array Response with submitters data
     */
    public function createSubmission(int $templateId, array $submitters, array $options = []): array
    {
        $payload = [
            'template_id' => $templateId,
            'send_email' => $options['send_email'] ?? true,
            'send_sms' => $options['send_sms'] ?? false,
            'order' => $options['order'] ?? 'preserved',
            'submitters' => $submitters,
        ];

        // Optional parameters
        if (isset($options['completed_redirect_url'])) {
            $payload['completed_redirect_url'] = $options['completed_redirect_url'];
        }
        if (isset($options['bcc_completed'])) {
            $payload['bcc_completed'] = $options['bcc_completed'];
        }
        if (isset($options['expire_at'])) {
            $payload['expire_at'] = $options['expire_at'];
        }
        if (isset($options['message'])) {
            $payload['message'] = $options['message'];
        }

        Log::info('DocuSeal: Creating submission', [
            'template_id' => $templateId,
            'submitters_count' => count($submitters),
        ]);

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->post("{$this->apiUrl}/submissions", $payload);

        if ($response->failed()) {
            Log::error('DocuSeal: Failed to create submission', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to create DocuSeal submission: ' . $response->body());
        }

        $data = $response->json();
        
        Log::info('DocuSeal: Submission created successfully', [
            'submission_id' => $data[0]['submission_id'] ?? null,
        ]);

        return $data;
    }

    /**
     * Get submission details and status
     * 
     * @param int $submissionId DocuSeal submission ID
     * @return array Submission data with status, submitters, documents
     */
    public function getSubmission(int $submissionId): array
    {
        Log::info('DocuSeal: Getting submission', ['submission_id' => $submissionId]);

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->get("{$this->apiUrl}/submissions/{$submissionId}");

        if ($response->failed()) {
            Log::error('DocuSeal: Failed to get submission', [
                'submission_id' => $submissionId,
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to get DocuSeal submission');
        }

        return $response->json();
    }

    /**
     * List all submissions with filters
     * 
     * @param array $filters (template_id, status, q, limit, etc.)
     * @return array Paginated list of submissions
     */
    public function listSubmissions(array $filters = []): array
    {
        Log::info('DocuSeal: Listing submissions', $filters);

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->get("{$this->apiUrl}/submissions", $filters);

        if ($response->failed()) {
            Log::error('DocuSeal: Failed to list submissions', [
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to list DocuSeal submissions');
        }

        return $response->json();
    }

    /**
     * Get submission documents (signed PDFs)
     * 
     * @param int $submissionId DocuSeal submission ID
     * @return array Documents array with name and URL
     */
    public function getSubmissionDocuments(int $submissionId): array
    {
        Log::info('DocuSeal: Getting submission documents', ['submission_id' => $submissionId]);

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->get("{$this->apiUrl}/submissions/{$submissionId}/documents");

        if ($response->failed()) {
            Log::error('DocuSeal: Failed to get documents', [
                'submission_id' => $submissionId,
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to get DocuSeal documents');
        }

        return $response->json();
    }

    /**
     * Download signed document
     * 
     * @param string $documentUrl URL from DocuSeal API
     * @return string Binary content of the PDF
     */
    public function downloadDocument(string $documentUrl): string
    {
        Log::info('DocuSeal: Downloading document', ['url' => $documentUrl]);

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->get($documentUrl);

        if ($response->failed()) {
            Log::error('DocuSeal: Failed to download document', [
                'url' => $documentUrl,
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to download document');
        }

        return $response->body();
    }

    /**
     * Update submitter (resend email, update fields, etc.)
     * 
     * @param int $submitterId DocuSeal submitter ID
     * @param array $data Update data (email, values, send_email, etc.)
     * @return array Updated submitter data
     */
    public function updateSubmitter(int $submitterId, array $data): array
    {
        Log::info('DocuSeal: Updating submitter', [
            'submitter_id' => $submitterId,
            'data' => $data,
        ]);

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->put("{$this->apiUrl}/submitters/{$submitterId}", $data);

        if ($response->failed()) {
            Log::error('DocuSeal: Failed to update submitter', [
                'submitter_id' => $submitterId,
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to update submitter');
        }

        return $response->json();
    }

    /**
     * Get submitter details
     * 
     * @param int $submitterId DocuSeal submitter ID
     * @return array Submitter data
     */
    public function getSubmitter(int $submitterId): array
    {
        Log::info('DocuSeal: Getting submitter', ['submitter_id' => $submitterId]);

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->get("{$this->apiUrl}/submitters/{$submitterId}");

        if ($response->failed()) {
            Log::error('DocuSeal: Failed to get submitter', [
                'submitter_id' => $submitterId,
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to get submitter');
        }

        return $response->json();
    }

    /**
     * Handle webhook event from DocuSeal
     * 
     * @param array $payload Webhook payload
     * @return void
     */
    public function handleWebhook(array $payload): void
    {
        $eventType = $payload['event_type'] ?? null;
        $data = $payload['data'] ?? [];

        Log::info('DocuSeal: Webhook received', [
            'event_type' => $eventType,
            'submission_id' => $data['submission_id'] ?? null,
            'submitter_id' => $data['id'] ?? null,
        ]);

        // Handle different event types
        match ($eventType) {
            'form.completed' => $this->handleFormCompleted($data),
            'form.declined' => $this->handleFormDeclined($data),
            'submission.completed' => $this->handleSubmissionCompleted($data),
            'submission.expired' => $this->handleSubmissionExpired($data),
            default => Log::warning('DocuSeal: Unknown webhook event type', ['event_type' => $eventType]),
        };
    }

    /**
     * Handle form.completed webhook event
     */
    protected function handleFormCompleted(array $data): void
    {
        $submissionId = $data['submission_id'] ?? $data['submission']['id'] ?? null;

        Log::info('DocuSeal: Form completed', [
            'submitter_email' => $data['email'] ?? null,
            'external_id' => $data['external_id'] ?? null,
            'submission_id' => $submissionId,
        ]);

        if (!$submissionId) {
            return;
        }

        // Locate our document by submission id
        $document = Document::where('docuseal_submission_id', $submissionId)->latest()->first();
        if (!$document) {
            Log::warning('DocuSeal: Document not found for submission', ['submission_id' => $submissionId]);
            return;
        }

        // Download signed documents and store the first one
        try {
            $docs = $this->getSubmissionDocuments((int)$submissionId);
            if (!empty($docs)) {
                $first = $docs[0];
                $content = $this->downloadDocument($first['url']);
                $signedPath = 'contracts/signed/contract_signed_' . $submissionId . '_' . time() . '.pdf';
                Storage::put($signedPath, $content);
                $document->markCompleted($signedPath);
            } else {
                $document->markCompleted();
            }
        } catch (\Throwable $e) {
            Log::error('DocuSeal: Failed to download/store signed document', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
            ]);
            $document->markCompleted();
        }
    }

    /**
     * Handle form.declined webhook event
     */
    protected function handleFormDeclined(array $data): void
    {
        // TODO: Implement business logic
        // - Update contract status to 'declined'
        // - Send notification to consultant
        // - Log activity with decline reason
        
        Log::info('DocuSeal: Form declined', [
            'submitter_email' => $data['email'] ?? null,
            'decline_reason' => $data['decline_reason'] ?? null,
        ]);
    }

    /**
     * Handle submission.completed webhook event
     */
    protected function handleSubmissionCompleted(array $data): void
    {
        $submissionId = $data['id'] ?? null;
        Log::info('DocuSeal: Submission completed', [
            'submission_id' => $submissionId,
        ]);

        if (!$submissionId) {
            return;
        }

        $document = Document::where('docuseal_submission_id', $submissionId)->latest()->first();
        if (!$document) {
            Log::warning('DocuSeal: Document not found for submission', ['submission_id' => $submissionId]);
            return;
        }

        try {
            $docs = $this->getSubmissionDocuments((int)$submissionId);
            if (!empty($docs)) {
                $first = $docs[0];
                $content = $this->downloadDocument($first['url']);
                $signedPath = 'contracts/signed/contract_signed_' . $submissionId . '_' . time() . '.pdf';
                Storage::put($signedPath, $content);
                $document->markCompleted($signedPath);
            } else {
                $document->markCompleted();
            }
        } catch (\Throwable $e) {
            Log::error('DocuSeal: Failed to download/store signed document on submission.completed', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
            ]);
            $document->markCompleted();
        }
    }

    /**
     * Handle submission.expired webhook event
     */
    protected function handleSubmissionExpired(array $data): void
    {
        $submissionId = $data['id'] ?? null;
        
        Log::info('DocuSeal: Submission expired', [
            'submission_id' => $submissionId,
        ]);

        if ($submissionId) {
            Document::where('docuseal_submission_id', $submissionId)
                ->update(['status' => 'expired']);
        }
    }

    /**
     * Test API connection
     * 
     * @return bool True if connection successful
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->apiUrl}/submissions", ['limit' => 1]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('DocuSeal: Connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
