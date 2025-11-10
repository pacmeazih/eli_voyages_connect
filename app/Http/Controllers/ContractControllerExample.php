<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Dossier;
use App\Services\DocuSealService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * EXAMPLE ContractController with DocuSeal Integration
 * 
 * This is a complete example showing how to integrate DocuSeal API
 * for contract generation and e-signature workflow.
 * 
 * Copy relevant methods to your existing ContractController.
 */
class ContractControllerExample extends Controller
{
    protected DocuSealService $docuSealService;

    public function __construct(DocuSealService $docuSealService)
    {
        $this->docuSealService = $docuSealService;
    }

    /**
     * Generate and send contract for signature
     * 
     * POST /contracts/generate/{dossier}
     */
    public function store(Request $request, Dossier $dossier)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:service,reservation,payment',
            'language' => 'required|string|in:fr,en,ar',
            'template_id' => 'required|integer',
            'variables' => 'array',
            'signers' => 'required|array|min:1',
            'signers.*.type' => 'required|string|in:client,guarantor',
            'signers.*.name' => 'required|string',
            'signers.*.email' => 'required|email',
            'signers.*.phone' => 'nullable|string',
        ]);

        // Create contract record
        $contract = Contract::create([
            'dossier_id' => $dossier->id,
            'type' => $validated['type'],
            'language' => $validated['language'],
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        try {
            // Prepare submitters for DocuSeal
            $submitters = collect($validated['signers'])->map(function ($signer, $index) use ($contract, $dossier) {
                return [
                    'role' => ucfirst($signer['type']),
                    'name' => $signer['name'],
                    'email' => $signer['email'],
                    'phone' => $signer['phone'] ?? null,
                    'external_id' => $signer['type'] . '_' . $contract->id,
                    'order' => $index,
                    'metadata' => [
                        'contract_id' => $contract->id,
                        'dossier_id' => $dossier->id,
                        'client_id' => $dossier->client_id,
                        'signer_type' => $signer['type'],
                    ],
                    'fields' => $this->prepareFields($dossier, $validated['variables'] ?? []),
                ];
            })->toArray();

            // Send to DocuSeal
            $response = $this->docuSealService->createSubmission(
                templateId: $validated['template_id'],
                submitters: $submitters,
                options: [
                    'send_email' => true,
                    'order' => 'preserved',
                    'completed_redirect_url' => route('contracts.completed', $contract),
                    'bcc_completed' => config('mail.from.address'),
                    'expire_at' => now()->addDays(30)->format('Y-m-d H:i:s') . ' UTC',
                    'message' => [
                        'subject' => __('Signature requise - Contrat :type', ['type' => $validated['type']]),
                        'body' => $this->getEmailBody($dossier),
                    ],
                ]
            );

            // Store DocuSeal data
            $contract->update([
                'docuseal_submission_id' => $response[0]['submission_id'],
                'docuseal_template_id' => $validated['template_id'],
                'docuseal_signers' => $response,
                'sent_for_signature_at' => now(),
                'status' => 'sent',
            ]);

            // Create contract signatures records
            foreach ($response as $submitter) {
                $contract->signatures()->create([
                    'signer_email' => $submitter['email'],
                    'signer_name' => $submitter['name'],
                    'signer_role' => strtolower($submitter['role']),
                    'docuseal_submitter_id' => $submitter['id'],
                    'status' => 'pending',
                    'sent_at' => now(),
                ]);
            }

            return redirect()->route('contracts.show', $contract)
                ->with('success', __('Contract sent successfully for signature.'));

        } catch (\Exception $e) {
            $contract->delete();
            
            return back()->withErrors([
                'error' => __('Failed to send contract: :message', ['message' => $e->getMessage()])
            ]);
        }
    }

    /**
     * Show contract details and signature status
     * 
     * GET /contracts/{contract}
     */
    public function show(Contract $contract)
    {
        // Refresh status from DocuSeal if needed
        if ($contract->docuseal_submission_id && $contract->status !== 'completed') {
            $this->syncSubmissionStatus($contract);
        }

        return Inertia::render('Contracts/Sign', [
            'contract' => $contract->load(['dossier.client', 'signatures']),
            'embedUrl' => $this->getEmbedUrl($contract),
        ]);
    }

    /**
     * Send reminder to pending signers
     * 
     * POST /contracts/{contract}/remind
     */
    public function remind(Contract $contract)
    {
        if ($contract->status === 'completed') {
            return back()->withErrors(['error' => __('Contract is already completed.')]);
        }

        try {
            $pendingSignatures = $contract->signatures()
                ->whereIn('status', ['pending', 'sent'])
                ->get();

            foreach ($pendingSignatures as $signature) {
                $this->docuSealService->updateSubmitter(
                    submitterId: $signature->docuseal_submitter_id,
                    data: ['send_email' => true]
                );
            }

            return back()->with('success', __('Reminder sent successfully.'));

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => __('Failed to send reminder: :message', ['message' => $e->getMessage()])
            ]);
        }
    }

    /**
     * Download signed contract
     * 
     * GET /contracts/{contract}/download
     */
    public function download(Contract $contract)
    {
        if ($contract->status !== 'completed') {
            return back()->withErrors(['error' => __('Contract is not yet completed.')]);
        }

        try {
            $documents = $this->docuSealService->getSubmissionDocuments(
                $contract->docuseal_submission_id
            );

            if (empty($documents['documents'])) {
                throw new \Exception('No documents found');
            }

            $document = $documents['documents'][0];
            $content = $this->docuSealService->downloadDocument($document['url']);

            $filename = $contract->dossier->client->name . '_' . 
                       $contract->type . '_' . 
                       $contract->created_at->format('Y-m-d') . '.pdf';

            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => __('Failed to download contract: :message', ['message' => $e->getMessage()])
            ]);
        }
    }

    /**
     * Webhook endpoint for DocuSeal events
     * 
     * POST /api/webhooks/docuseal
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        
        try {
            $this->docuSealService->handleWebhook($payload);
            
            $eventType = $payload['event_type'] ?? null;
            $data = $payload['data'] ?? [];

            switch ($eventType) {
                case 'form.completed':
                    $this->handleFormCompleted($data);
                    break;
                    
                case 'form.declined':
                    $this->handleFormDeclined($data);
                    break;
                    
                case 'submission.completed':
                    $this->handleSubmissionCompleted($data);
                    break;
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('DocuSeal webhook error: ' . $e->getMessage(), [
                'payload' => $payload,
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========== PROTECTED HELPER METHODS ==========

    protected function handleFormCompleted(array $data): void
    {
        $signature = \DB::table('contract_signatures')
            ->where('signer_email', $data['email'])
            ->where('status', '!=', 'signed')
            ->first();

        if ($signature) {
            \DB::table('contract_signatures')
                ->where('id', $signature->id)
                ->update([
                    'status' => 'signed',
                    'signed_at' => $data['completed_at'] ?? now(),
                    'ip_address' => $data['ip'] ?? null,
                    'updated_at' => now(),
                ]);
        }
    }

    protected function handleFormDeclined(array $data): void
    {
        $signature = \DB::table('contract_signatures')
            ->where('signer_email', $data['email'])
            ->first();

        if ($signature) {
            \DB::table('contract_signatures')
                ->where('id', $signature->id)
                ->update([
                    'status' => 'declined',
                    'updated_at' => now(),
                ]);

            $contract = Contract::find($data['metadata']['contract_id'] ?? null);
            if ($contract) {
                $contract->update(['status' => 'declined']);
            }
        }
    }

    protected function handleSubmissionCompleted(array $data): void
    {
        $contract = Contract::where('docuseal_submission_id', $data['id'])->first();

        if ($contract) {
            // Download and store signed document
            $documents = $this->docuSealService->getSubmissionDocuments($data['id']);
            
            if (!empty($documents['documents'])) {
                $document = $documents['documents'][0];
                $content = $this->docuSealService->downloadDocument($document['url']);
                
                $filename = 'contracts/' . $contract->id . '_signed_' . time() . '.pdf';
                Storage::disk('local')->put($filename, $content);
                
                $contract->update([
                    'signed_document_path' => $filename,
                ]);
            }

            $contract->update([
                'status' => 'completed',
                'completed_at' => $data['completed_at'] ?? now(),
            ]);
        }
    }

    protected function prepareFields(Dossier $dossier, array $variables): array
    {
        $fields = [
            [
                'name' => 'Client Name',
                'default_value' => $dossier->client->name,
                'readonly' => true,
            ],
            [
                'name' => 'Package',
                'default_value' => $dossier->package->name ?? '',
                'readonly' => true,
            ],
            [
                'name' => 'Total Amount',
                'default_value' => number_format($dossier->package->price ?? 0, 2) . ' €',
                'readonly' => true,
            ],
            [
                'name' => 'Departure Date',
                'default_value' => $dossier->package->departure_date?->format('d/m/Y') ?? '',
                'readonly' => true,
            ],
        ];

        foreach ($variables as $key => $value) {
            $fields[] = [
                'name' => $key,
                'default_value' => $value,
                'readonly' => false,
            ];
        }

        return $fields;
    }

    protected function getEmailBody(Dossier $dossier): string
    {
        return "Bonjour,\n\n" .
               "Veuillez signer le contrat pour votre voyage avec ELI-Voyages.\n\n" .
               "Package: " . ($dossier->package->name ?? 'N/A') . "\n" .
               "Dossier: #" . $dossier->reference . "\n\n" .
               "Cliquez sur le lien ci-dessous pour accéder au document:\n" .
               "{{submitter.link}}\n\n" .
               "Cordialement,\n" .
               "L'équipe ELI-Voyages";
    }

    protected function getEmbedUrl(Contract $contract): ?string
    {
        if (!$contract->docuseal_signers) {
            return null;
        }

        $userEmail = auth()->user()->email;
        
        foreach ($contract->docuseal_signers as $signer) {
            if ($signer['email'] === $userEmail) {
                return $signer['embed_src'] ?? null;
            }
        }

        return $contract->docuseal_signers[0]['embed_src'] ?? null;
    }

    protected function syncSubmissionStatus(Contract $contract): void
    {
        try {
            $submission = $this->docuSealService->getSubmission(
                $contract->docuseal_submission_id
            );

            if ($submission['status'] === 'completed' && $contract->status !== 'completed') {
                $contract->update([
                    'status' => 'completed',
                    'completed_at' => $submission['completed_at'] ?? now(),
                ]);
            }

            foreach ($submission['submitters'] as $submitter) {
                $contract->signatures()
                    ->where('signer_email', $submitter['email'])
                    ->update([
                        'status' => $submitter['status'],
                        'signed_at' => $submitter['completed_at'] ?? null,
                    ]);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to sync submission status', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
