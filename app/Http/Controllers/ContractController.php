<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Package;
use App\Models\Document;
use App\Services\ContractGeneratorService;
use App\Services\DocuSealService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ContractController extends Controller
{
    protected ContractGeneratorService $contractService;
    protected DocuSealService $docuSeal;

    public function __construct(ContractGeneratorService $contractService, DocuSealService $docuSeal)
    {
        $this->contractService = $contractService;
        $this->docuSeal = $docuSeal;
    }

    /**
     * Show the contract generation page
     */
    public function create(Dossier $dossier)
    {
        $dossier->load(['client', 'package', 'documents' => function ($query) {
            $query->where('type', 'contract')->latest()->take(5);
        }]);

        return Inertia::render('Contracts/Generate', [
            'dossier' => $dossier,
            'client' => $dossier->client,
            'package' => $dossier->package,
            'generatedContracts' => $dossier->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'type' => $doc->name,
                    'created_at' => $doc->created_at,
                ];
            }),
        ]);
    }

    /**
     * Generate a contract for a dossier/package
     */
    public function generate(Request $request, Dossier $dossier)
    {
        $validated = $request->validate([
            'contract_type' => 'required|string',
            'language' => 'required|in:fr,en',
            'variables' => 'required|array',
        ]);

        try {
            // Prepare contract variables from dossier data
            $variables = $this->prepareContractVariables($dossier, $validated['variables']);

            // Generate contract
            $contractPath = $this->contractService->generateContract(
                $validated['contract_type'],
                $variables,
                $validated['language']
            );

            // Store contract reference in database
            $dossier->documents()->create([
                'type' => 'contract',
                'name' => basename($contractPath),
                'path' => str_replace(storage_path('app/'), '', $contractPath),
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size' => filesize($contractPath),
                'uploaded_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.contract_generated_successfully'),
                'contract_path' => $contractPath,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contract_generation_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * New flow: Generate + Send for e-signature (DocuSeal)
     * Compatible with resources/js/Pages/Contracts/GenerateEnhanced.vue
     */
    public function store(Request $request, Dossier $dossier)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'language' => 'required|in:fr,en,ar',
            'variables' => 'required|array',
            'template_id' => 'nullable|integer',
            'signers' => 'required|array|min:1',
            'signers.*.type' => 'required|string',
            'signers.*.name' => 'required|string',
            'signers.*.email' => 'required|email',
            'signers.*.phone' => 'nullable|string',
        ]);

        $dossier->load('client', 'package');

        // 1) Prepare variables and generate PDF from Word template (for archive/preview)
        $variables = $this->prepareContractVariables($dossier, $validated['variables']);
        $generatedPath = $this->contractService->generateContract(
            $validated['type'],
            $variables,
            'pdf'
        );

        // Persist as a Document linked to the dossier
        $document = $dossier->documents()->create([
            'type' => 'contract',
            'name' => basename($generatedPath),
            'path' => $generatedPath, // already relative to storage/app in our service
            'mime_type' => 'application/pdf',
            'size' => Storage::size($generatedPath) ?? 0,
            'uploaded_by' => auth()->id(),
            'description' => 'Contrat généré (avant signature)',
        ]);

        // 2) Send for e-signature using DocuSeal templates
        $templateId = $validated['template_id']
            ?? Config::get('docuseal.templates.' . $validated['type']);

        if (!$templateId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun template DocuSeal configuré pour ce type de contrat.',
            ], 422);
        }

        // Map signers to DocuSeal submitters format
        // IMPORTANT: Consultant must sign FIRST, then client
        $submitters = collect($validated['signers'])
            ->sortBy(function ($signer) {
                // Consultant gets order 0 (first), client gets order 1 (second)
                return $signer['type'] === 'consultant' ? 0 : 1;
            })
            ->values()
            ->map(function ($signer, $index) use ($dossier, $variables) {
                return [
                    'role' => $signer['type'], // must match DocuSeal role in template
                    'name' => $signer['name'],
                    'email' => $signer['email'],
                    'phone' => $signer['phone'] ?? null,
                    'order' => $index, // Ensures sequential signing
                    'values' => [
                        // Optional prefill values for DocuSeal template fields
                        // Example: ['field_name' => 'value']
                    ],
                    'external_id' => 'dossier:' . $dossier->id,
                ];
            })
            ->toArray();

        $options = [
            'completed_redirect_url' => route('contracts.show', $dossier->id),
            'message' => 'Veuillez signer le contrat pour le dossier #' . ($dossier->numero ?? $dossier->id),
        ];

        $submission = $this->docuSeal->createSubmission($templateId, $submitters, $options);

        // The API returns an array of submitters with submission_id and embed_src
        $submissionId = $submission[0]['submission_id'] ?? null;
        $embedUrl = $submission[0]['embed_src'] ?? null;

        // Update our document with DocuSeal metadata (requires columns on documents table)
        if (Schema::hasColumn('documents', 'docuseal_submission_id')) {
            $document->update([
                'docuseal_submission_id' => $submissionId,
                'docuseal_template_id' => $templateId,
                'docuseal_signers' => $validated['signers'],
                'consultant_id' => collect($validated['signers'])
                    ->firstWhere('type', 'consultant')['user_id'] ?? auth()->id(),
                'sent_for_signature_at' => now(),
                'status' => 'sent',
            ]);
        }

        // Return data for frontend to redirect to Sign.vue
        return response()->json([
            'success' => true,
            'document_id' => $document->id,
            'submission_id' => $submissionId,
            'embed_url' => $embedUrl,
            'message' => 'Contrat généré et envoyé pour signature',
        ]);
    }

    /**
     * Show signature page
     */
    public function show(Dossier $dossier)
    {
        $latestContract = $dossier->documents()->ofType('contract')->latest()->first();

        return Inertia::render('Contracts/Sign', [
            'contract' => [
                'id' => $latestContract?->id,
                'status' => $latestContract?->status ?? 'sent',
                'dossier' => [
                    'id' => $dossier->id,
                    'reference' => $dossier->numero ?? (string)$dossier->id,
                    'client' => [
                        'name' => $dossier->client?->full_name,
                    ],
                ],
                'signatures' => [], // could be filled from a tracking table later
            ],
            'embedUrl' => null, // Frontend can receive from store() response
        ]);
    }

    /**
     * Download a generated contract
     */
    public function download(Dossier $dossier, $documentId)
    {
        $document = $dossier->documents()->findOrFail($documentId);

        if ($document->type !== 'contract') {
            abort(404);
        }

        $filePath = storage_path("app/{$document->path}");

        if (!file_exists($filePath)) {
            abort(404, 'Contract file not found');
        }

        return response()->download($filePath, $document->name);
    }

    /**
     * Preview contract before generation
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'contract_type' => 'required|string',
            'language' => 'required|in:fr,en',
            'dossier_id' => 'required|exists:dossiers,id',
        ]);

        $dossier = Dossier::with('client', 'package')->findOrFail($validated['dossier_id']);

        // Get contract template text
        $contractFilename = $this->contractService->getContractTemplateFilename(
            $validated['contract_type'],
            $validated['language']
        );

        if (!$contractFilename) {
            abort(404, 'Contract type not found');
        }

        $textTemplatePath = base_path("models_contrat/{$contractFilename}");

        if (!file_exists($textTemplatePath)) {
            abort(404, 'Contract template not found');
        }

        $contractText = file_get_contents($textTemplatePath);

        // Get available variables
        $availableVariables = $this->getAvailableVariables($dossier);

        return Inertia::render('Contracts/Preview', [
            'dossier' => $dossier,
            'contractText' => $contractText,
            'availableVariables' => $availableVariables,
            'contractType' => $validated['contract_type'],
            'language' => $validated['language'],
        ]);
    }

    /**
     * Prepare contract variables from dossier data
     */
    protected function prepareContractVariables(Dossier $dossier, array $customVariables = []): array
    {
        $client = $dossier->client;
        $package = $dossier->package;

        $defaultVariables = [
            // Client info
            'client_civilite' => $client->civility ?? 'M.',
            'client_nom' => $client->last_name ?? '',
            'client_prenom' => $client->first_name ?? '',
            'client_nom_complet' => $client->full_name ?? '',
            'client_full_name' => $client->full_name ?? '',
            'client_adresse' => $client->address ?? '',
            'client_address' => $client->address ?? '',
            'client_cin_numero' => $client->id_number ?? '',
            'client_id_number' => $client->id_number ?? '',
            'client_cin_lieu' => $client->id_place ?? '',
            'client_id_place' => $client->id_place ?? '',
            'client_cin_date' => $client->id_date?->format('d/m/Y') ?? '',
            'client_id_date' => $client->id_date?->format('d/m/Y') ?? '',
            'client_cin_expiration' => $client->id_expiry?->format('d/m/Y') ?? '',
            'client_id_expiry' => $client->id_expiry?->format('d/m/Y') ?? '',
            'client_telephone' => $client->phone ?? '',
            'client_phone' => $client->phone ?? '',
            'client_email' => $client->email ?? '',
            'client_passeport_numero' => $client->passport_number ?? '',
            'client_passport_number' => $client->passport_number ?? '',

            // Dossier info
            'numero_dossier' => $dossier->numero ?? '',
            'type_service' => $package->service_type ?? '',
            'session_universitaire' => $package->academic_session ?? '',
            'duree_contrat' => $package->contract_duration ?? '12 mois',
            'contract_duration' => $package->contract_duration ?? '12 months',

            // Financial
            'montant_total' => number_format($package->total_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'total_amount' => number_format($package->total_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'montant_admission' => number_format($package->admission_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'montant_permis' => number_format($package->permit_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'montant_ouverture' => number_format($package->opening_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'opening_amount' => number_format($package->opening_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'montant_soumission' => number_format($package->submission_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'submission_amount' => number_format($package->submission_amount ?? 0, 0, ',', ' ') . ' F CFA',
            'depot_initial' => number_format($package->initial_deposit ?? 500000, 0, ',', ' ') . ' F CFA',
            'initial_deposit' => number_format($package->initial_deposit ?? 500000, 0, ',', ' ') . ' F CFA',

            // Dates
            'date_signature' => now()->translatedFormat('d F Y'),
            'signature_date' => now()->translatedFormat('F d, Y'),
            'date_limite_signature' => now()->addDays(14)->translatedFormat('d F Y'),

            // Agent
            'agent_nom' => auth()->user()->last_name ?? '',
            'agent_prenom' => auth()->user()->first_name ?? '',
        ];

        // Merge with custom variables (custom takes precedence)
        return array_merge($defaultVariables, $customVariables);
    }

    /**
     * Get all available variables for a dossier
     */
    protected function getAvailableVariables(Dossier $dossier): array
    {
        return [
            'client' => [
                'civilite', 'nom', 'prenom', 'nom_complet', 'adresse', 
                'cin_numero', 'cin_lieu', 'cin_date', 'cin_expiration',
                'telephone', 'email', 'passeport_numero'
            ],
            'dossier' => [
                'numero_dossier', 'type_service', 'session_universitaire', 'duree_contrat'
            ],
            'financial' => [
                'montant_total', 'montant_admission', 'montant_permis',
                'montant_ouverture', 'montant_soumission', 'depot_initial'
            ],
            'dates' => [
                'date_signature', 'date_limite_signature'
            ],
            'agent' => [
                'agent_nom', 'agent_prenom'
            ],
        ];
    }
}
