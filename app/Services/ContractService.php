<?php<?php



namespace App\Services;namespace App\Services;



use App\Models\Client;use App\Models\Dossier;

use App\Models\Document;use GuzzleHttp\Client;

use App\Models\Dossier;use Illuminate\Support\Facades\Log;

use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;class ContractService

use Illuminate\Support\Facades\Log;{

    protected Client $client;

/**    protected string $apiUrl;

 * Service for managing contracts with OOP approach    protected string $apiKey;

 * Handles contract listing, status tracking, and DocuSeal integration

 */    public function __construct()

class ContractService    {

{        $this->apiUrl = config('services.docuseal.api_url', 'https://api.docuseal.co');

    protected DocuSealService $docuSealService;        $this->apiKey = config('services.docuseal.api_key');

        

    public function __construct(DocuSealService $docuSealService)        $this->client = new Client([

    {            'base_uri' => $this->apiUrl,

        $this->docuSealService = $docuSealService;            'headers' => [

    }                'X-Auth-Token' => $this->apiKey,

                'Accept' => 'application/json',

    /**                'Content-Type' => 'application/json',

     * Get contracts for a specific client with enriched data            ],

     */            'timeout' => 30,

    public function getClientContracts(int $clientId): Collection        ]);

    {    }

        return Document::where('type', 'contract')

            ->whereHas('dossier', function ($query) use ($clientId) {    /**

                $query->where('client_id', $clientId);     * Generate a contract from a DOCX template with variables

            })     */

            ->with(['dossier.client', 'dossier.package'])    public function generateFromTemplate(

            ->latest()        string $templatePath,

            ->get()        array $variables,

            ->map(function ($contract) {        Dossier $dossier

                return $this->enrichContractData($contract);    ): array {

            });        try {

    }            $response = $this->client->post('/submissions', [

                'multipart' => [

    /**                    [

     * Get all contracts (for staff)                        'name' => 'file',

     */                        'contents' => fopen($templatePath, 'r'),

    public function getAllContracts(): Collection                        'filename' => basename($templatePath),

    {                    ],

        return Document::where('type', 'contract')                    [

            ->with(['dossier.client', 'dossier.package'])                        'name' => 'data',

            ->latest()                        'contents' => json_encode($variables),

            ->get()                    ],

            ->map(function ($contract) {                ],

                return $this->enrichContractData($contract);            ]);

            });

    }            $result = json_decode($response->getBody()->getContents(), true);



    /**            // Log activity

     * Enrich contract data with signature status and actions            activity()

     */                ->performedOn($dossier)

    protected function enrichContractData(Document $contract): array                ->causedBy(auth()->user())

    {                ->withProperties(['contract_id' => $result['id'] ?? null])

        $signatureStatus = $this->getSignatureStatus($contract);                ->log('Contract generated from template');

        

        return [            return $result;

            'id' => $contract->id,        } catch (\Exception $e) {

            'name' => $contract->name,            Log::error('DocuSeal contract generation failed', [

            'reference' => $contract->dossier?->reference ?? 'N/A',                'error' => $e->getMessage(),

            'client' => $contract->dossier?->client ? [                'dossier_id' => $dossier->id,

                'id' => $contract->dossier->client->id,            ]);

                'nom' => $contract->dossier->client->nom,            throw $e;

                'prenom' => $contract->dossier->client->prenom,        }

                'full_name' => $contract->dossier->client->full_name,    }

                'email' => $contract->dossier->client->email,

            ] : null,    /**

            'package' => $contract->dossier?->package ? [     * Create a submission request for signing

                'id' => $contract->dossier->package->id,     */

                'name' => $contract->dossier->package->name,    public function createSignatureRequest(

            ] : null,        int $templateId,

            'created_at' => $contract->created_at,        array $submitters,

            'size' => $contract->size,        Dossier $dossier

            'path' => $contract->path,    ): array {

                    try {

            // Signature data            $response = $this->client->post('/submissions', [

            'signature_status' => $signatureStatus['status'],                'json' => [

            'signature_status_label' => $signatureStatus['label'],                    'template_id' => $templateId,

            'signature_status_color' => $signatureStatus['color'],                    'send_email' => true,

            'docuseal_submission_id' => $contract->docuseal_submission_id,                    'submitters' => $submitters,

            'docuseal_sign_url' => $signatureStatus['sign_url'],                ],

            'can_sign' => $signatureStatus['can_sign'],            ]);

            'signed_at' => $contract->signature_completed_at ?? null,

                        $result = json_decode($response->getBody()->getContents(), true);

            // Actions

            'can_view' => !empty($contract->path),            // Log activity

            'can_download' => !empty($contract->path),            activity()

            'can_initiate_signature' => empty($contract->docuseal_submission_id),                ->performedOn($dossier)

        ];                ->causedBy(auth()->user())

    }                ->withProperties([

                    'submission_id' => $result['id'] ?? null,

    /**                    'submitters' => count($submitters),

     * Get signature status for a contract                ])

     */                ->log('Signature request sent');

    protected function getSignatureStatus(Document $contract): array

    {            return $result;

        // No DocuSeal submission yet        } catch (\Exception $e) {

        if (empty($contract->docuseal_submission_id)) {            Log::error('DocuSeal signature request failed', [

            return [                'error' => $e->getMessage(),

                'status' => 'not_sent',                'dossier_id' => $dossier->id,

                'label' => 'Non envoyé pour signature',            ]);

                'color' => 'gray',            throw $e;

                'sign_url' => null,        }

                'can_sign' => false,    }

            ];

        }    /**

     * Get submission status

        // Check signature_status field in document     */

        $status = $contract->signature_status ?? 'pending';    public function getSubmissionStatus(string $submissionId): array

    {

        switch ($status) {        try {

            case 'completed':            $response = $this->client->get("/submissions/{$submissionId}");

                return [            return json_decode($response->getBody()->getContents(), true);

                    'status' => 'completed',        } catch (\Exception $e) {

                    'label' => 'Signé',            Log::error('DocuSeal status check failed', [

                    'color' => 'green',                'error' => $e->getMessage(),

                    'sign_url' => null,                'submission_id' => $submissionId,

                    'can_sign' => false,            ]);

                ];            throw $e;

                    }

            case 'pending':    }

                // Try to get sign URL from DocuSeal

                $signUrl = $this->getDocuSealSignUrl($contract);    /**

                return [     * List all templates

                    'status' => 'pending',     */

                    'label' => 'En attente de signature',    public function listTemplates(): array

                    'color' => 'yellow',    {

                    'sign_url' => $signUrl,        try {

                    'can_sign' => !empty($signUrl),            $response = $this->client->get('/templates');

                ];            return json_decode($response->getBody()->getContents(), true);

                    } catch (\Exception $e) {

            case 'expired':            Log::error('DocuSeal templates list failed', [

                return [                'error' => $e->getMessage(),

                    'status' => 'expired',            ]);

                    'label' => 'Expiré',            throw $e;

                    'color' => 'red',        }

                    'sign_url' => null,    }

                    'can_sign' => false,

                ];    /**

                 * Process webhook from DocuSeal

            default:     */

                return [    public function processWebhook(array $payload): void

                    'status' => 'unknown',    {

                    'label' => 'Statut inconnu',        $eventType = $payload['event_type'] ?? null;

                    'color' => 'gray',        $submissionId = $payload['submission_id'] ?? null;

                    'sign_url' => null,

                    'can_sign' => false,        Log::info('DocuSeal webhook received', [

                ];            'event_type' => $eventType,

        }            'submission_id' => $submissionId,

    }        ]);



    /**        switch ($eventType) {

     * Get DocuSeal sign URL for a contract            case 'form.completed':

     */                $this->handleFormCompleted($payload);

    protected function getDocuSealSignUrl(Document $contract): ?string                break;

    {            case 'form.viewed':

        if (empty($contract->docuseal_submission_id)) {                $this->handleFormViewed($payload);

            return null;                break;

        }            case 'form.started':

                $this->handleFormStarted($payload);

        try {                break;

            // Get submission details from DocuSeal            default:

            $submission = $this->docuSealService->getSubmission($contract->docuseal_submission_id);                Log::info('Unhandled DocuSeal webhook event', ['event' => $eventType]);

                    }

            // Find the submitter (usually first one for client)    }

            if (isset($submission['submitters'][0]['embed_src'])) {

                return $submission['submitters'][0]['embed_src'];    /**

            }     * Handle form completed event

                 */

            return null;    protected function handleFormCompleted(array $payload): void

        } catch (\Exception $e) {    {

            Log::error('Failed to get DocuSeal sign URL', [        $submissionId = $payload['submission_id'] ?? null;

                'contract_id' => $contract->id,        $submitter = $payload['submitter'] ?? [];

                'submission_id' => $contract->docuseal_submission_id,        

                'error' => $e->getMessage(),        if (!$submissionId) {

            ]);            return;

            return null;        }

        }

    }        // Find document by submission ID

        $document = \App\Models\Document::where('docuseal_submission_id', $submissionId)->first();

    /**        

     * Initiate signature process for a contract        if (!$document) {

     */            Log::warning('Document not found for submission', ['submission_id' => $submissionId]);

    public function initiateSignature(Document $contract, array $signers, array $options = []): array            return;

    {        }

        if (!$contract->docuseal_template_id) {

            throw new \Exception('Ce contrat n\'a pas de template DocuSeal configuré');        // Check if this is a consultant signing

        }        $role = $submitter['role'] ?? null;

        if ($role === 'consultant' && !$document->consultant_signed_at) {

        try {            $document->update([

            // Create DocuSeal submission                'consultant_signed_at' => now(),

            $submission = $this->docuSealService->createSubmission(            ]);

                $contract->docuseal_template_id,            

                $signers,            activity()

                $options                ->performedOn($document)

            );                ->withProperties([

                    'submitter' => $submitter,

            // Update contract with submission ID                    'role' => 'consultant',

            $contract->update([                ])

                'docuseal_submission_id' => $submission[0]['submission_id'] ?? null,                ->log('Contract signed by consultant');

                'signature_status' => 'pending',                

                'signature_sent_at' => now(),            // Notify client that consultant has signed and it's their turn

            ]);            // TODO: Send notification to client

        }

            // Log activity        

            activity()        // Check if all signers have completed (form.completed means ALL signers done)

                ->performedOn($contract)        if (isset($payload['status']) && $payload['status'] === 'completed') {

                ->causedBy(auth()->user())            $document->update([

                ->log('Contrat envoyé pour signature via DocuSeal');                'status' => 'completed',

                'completed_at' => now(),

            return [            ]);

                'success' => true,            

                'submission' => $submission,            activity()

                'sign_url' => $submission[0]['embed_src'] ?? null,                ->performedOn($document)

            ];                ->withProperties($payload)

                ->log('Contract fully signed by all parties');

        } catch (\Exception $e) {                

            Log::error('Failed to initiate signature', [            // TODO: Store final signed PDF

                'contract_id' => $contract->id,            // TODO: Send completion notifications

                'error' => $e->getMessage(),        }

            ]);    }



            throw new \Exception('Erreur lors de l\'envoi du contrat pour signature: ' . $e->getMessage());    /**

        }     * Handle form viewed event

    }     */

    protected function handleFormViewed(array $payload): void

    /**    {

     * Update contract status from DocuSeal webhook        activity()

     */            ->withProperties($payload)

    public function updateFromWebhook(array $webhookData): bool            ->log('Contract viewed by signer');

    {    }

        try {

            $submissionId = $webhookData['submission_id'] ?? null;    /**

                 * Handle form started event

            if (!$submissionId) {     */

                Log::warning('Webhook without submission_id', $webhookData);    protected function handleFormStarted(array $payload): void

                return false;    {

            }        activity()

            ->withProperties($payload)

            // Find contract by submission ID            ->log('Contract signing started');

            $contract = Document::where('docuseal_submission_id', $submissionId)->first();    }

            

            if (!$contract) {    /**

                Log::warning('Contract not found for submission', ['submission_id' => $submissionId]);     * Validate webhook signature

                return false;     */

            }    public function validateWebhookSignature(string $payload, string $signature): bool

    {

            // Update signature status        // TODO: Implement signature validation based on DocuSeal documentation

            $eventType = $webhookData['event_type'] ?? null;        // For now, return true in development

                    if (config('app.env') === 'local') {

            switch ($eventType) {            return true;

                case 'form.completed':        }

                    $contract->update([

                        'signature_status' => 'completed',        $expectedSignature = hash_hmac('sha256', $payload, $this->apiKey);

                        'signature_completed_at' => now(),        return hash_equals($expectedSignature, $signature);

                    ]);    }

                    }

                    // Log activity
                    activity()
                        ->performedOn($contract)
                        ->log('Contrat signé avec succès');
                    break;
                
                case 'form.viewed':
                    // Client has viewed the signature form
                    activity()
                        ->performedOn($contract)
                        ->log('Contrat consulté par le client');
                    break;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update contract from webhook', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get contracts statistics for a client
     */
    public function getClientStats(int $clientId): array
    {
        $contracts = $this->getClientContracts($clientId);

        return [
            'total' => $contracts->count(),
            'pending_signature' => $contracts->where('signature_status', 'pending')->count(),
            'signed' => $contracts->where('signature_status', 'completed')->count(),
            'not_sent' => $contracts->where('signature_status', 'not_sent')->count(),
            'expired' => $contracts->where('signature_status', 'expired')->count(),
        ];
    }
}
