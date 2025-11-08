<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ClientTrackingController extends Controller
{
    /**
     * Display the client tracking dashboard
     */
    public function index(Request $request)
    {
        // Get authenticated user
        $user = auth()->user();

        // If user has Client role, get their client record
        if ($user->hasRole('Client')) {
            // Find the client record linked to this user
            // Assuming Client model has 'user_id' or email match
            $client = Client::where('email', $user->email)->first();

            if (!$client) {
                return Inertia::render('ClientTracking/NoAccess', [
                    'message' => 'Aucun dossier client trouvé pour cet utilisateur.',
                ]);
            }

            // Get the client's dossiers
            $dossiers = Dossier::where('client_id', $client->id)
                ->with(['client', 'package'])
                ->get();

            // If client has only one dossier, show its tracking directly
            if ($dossiers->count() === 1) {
                return $this->show($dossiers->first()->id);
            }

            // If multiple dossiers, show selection page
            return Inertia::render('ClientTracking/Select', [
                'dossiers' => $dossiers,
            ]);
        }

        // For non-client users (Admin/Agent), redirect to dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Show tracking for a specific dossier
     */
    public function show($dossierId)
    {
        $user = auth()->user();
        $dossier = Dossier::with(['client', 'package', 'documents'])->findOrFail($dossierId);

        // Security check: ensure client can only see their own dossier
        if ($user->hasRole('Client')) {
            $client = Client::where('email', $user->email)->first();
            if (!$client || $dossier->client_id !== $client->id) {
                abort(403, 'Accès non autorisé à ce dossier.');
            }
        }

        // Build timeline steps based on dossier status
        $timelineSteps = $this->buildTimelineSteps($dossier);

        // Get recent activities for this dossier
        $recentActivities = Activity::where('subject_type', Dossier::class)
            ->where('subject_id', $dossier->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('ClientTracking/Index', [
            'dossier' => $dossier,
            'timelineSteps' => $timelineSteps,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Build timeline steps based on dossier status and documents
     */
    private function buildTimelineSteps(Dossier $dossier)
    {
        $steps = [
            [
                'title' => 'Dossier créé',
                'description' => 'Votre dossier a été créé et est en cours de préparation.',
                'status' => 'completed',
                'date' => $dossier->created_at,
                'documents' => [],
                'actions' => [],
            ],
            [
                'title' => 'Documents requis',
                'description' => 'Téléchargez tous les documents nécessaires pour votre dossier.',
                'status' => $this->getStepStatus($dossier, 'documents'),
                'date' => null,
                'documents' => $this->getRequiredDocuments($dossier),
                'actions' => [
                    [
                        'label' => 'Télécharger des documents',
                        'type' => 'upload',
                    ],
                ],
            ],
            [
                'title' => 'Vérification des documents',
                'description' => 'Notre équipe vérifie vos documents.',
                'status' => $this->getStepStatus($dossier, 'verification'),
                'date' => null,
                'documents' => [],
                'actions' => [],
            ],
            [
                'title' => 'Préparation du contrat',
                'description' => 'Le contrat est en cours de préparation.',
                'status' => $this->getStepStatus($dossier, 'contract'),
                'date' => null,
                'documents' => [],
                'actions' => [],
            ],
            [
                'title' => 'Signature du contrat',
                'description' => 'Signez votre contrat électroniquement.',
                'status' => $this->getStepStatus($dossier, 'signature'),
                'date' => null,
                'documents' => $this->getContractDocuments($dossier),
                'actions' => [],
            ],
            [
                'title' => 'Traitement en cours',
                'description' => 'Votre dossier est en cours de traitement par les autorités compétentes.',
                'status' => $this->getStepStatus($dossier, 'processing'),
                'date' => null,
                'documents' => [],
                'actions' => [],
            ],
            [
                'title' => 'Dossier finalisé',
                'description' => 'Votre dossier a été approuvé et finalisé.',
                'status' => $this->getStepStatus($dossier, 'completed'),
                'date' => $dossier->status === 'completed' ? $dossier->updated_at : null,
                'documents' => [],
                'actions' => [],
            ],
        ];

        return $steps;
    }

    /**
     * Get step status based on dossier status
     */
    private function getStepStatus(Dossier $dossier, string $stepType): string
    {
        $statusMap = [
            'draft' => ['documents' => 'current'],
            'pending' => ['documents' => 'completed', 'verification' => 'current'],
            'in_progress' => [
                'documents' => 'completed',
                'verification' => 'completed',
                'contract' => 'completed',
                'signature' => 'completed',
                'processing' => 'current',
            ],
            'approved' => [
                'documents' => 'completed',
                'verification' => 'completed',
                'contract' => 'completed',
                'signature' => 'completed',
                'processing' => 'completed',
                'completed' => 'current',
            ],
            'completed' => [
                'documents' => 'completed',
                'verification' => 'completed',
                'contract' => 'completed',
                'signature' => 'completed',
                'processing' => 'completed',
                'completed' => 'completed',
            ],
            'rejected' => [
                'documents' => 'completed',
                'verification' => 'completed',
            ],
        ];

        $status = $statusMap[$dossier->status][$stepType] ?? 'pending';

        return $status;
    }

    /**
     * Get required documents for the dossier
     */
    private function getRequiredDocuments(Dossier $dossier)
    {
        // Get all documents for this dossier
        $documents = $dossier->documents()->get();

        return $documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'name' => $doc->title,
                'uploaded' => $doc->file_path !== null,
            ];
        })->toArray();
    }

    /**
     * Get contract documents
     */
    private function getContractDocuments(Dossier $dossier)
    {
        // Get documents with type 'contract'
        $contracts = $dossier->documents()
            ->where('type', 'contract')
            ->get();

        return $contracts->map(function ($doc) {
            return [
                'id' => $doc->id,
                'name' => $doc->title,
                'uploaded' => $doc->file_path !== null,
            ];
        })->toArray();
    }
}
