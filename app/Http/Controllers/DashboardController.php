<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Document;
use App\Models\Client;
use App\Services\ClientService;
use App\Services\DossierService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    protected ClientService $clientService;
    protected DossierService $dossierService;

    public function __construct(ClientService $clientService, DossierService $dossierService)
    {
        $this->clientService = $clientService;
        $this->dossierService = $dossierService;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        // If user is a client, return client-specific dashboard data
        if ($user->hasRole('Client')) {
            return $this->clientDashboard($user);
        }

        // For staff users (Admin, Agent, Consultant, Super Admin)
        return $this->staffDashboard();
    }

    /**
     * Client dashboard with personalized data
     */
    private function clientDashboard($user)
    {
        // Find the client record using service
        $client = $this->clientService->findByClientCodeOrEmail($user->email);

        if (!$client) {
            return inertia('Dashboard/Roles/Client', [
                'stats' => [
                    'currentDossier' => null,
                    'uploadedDocuments' => 0,
                    'missingDocuments' => 0,
                    'nextStep' => 'Aucun dossier trouvé',
                ],
                'recentActivity' => [],
                'pendingActions' => [],
                'upcomingAppointments' => [],
            ]);
        }

        // Get client statistics using service
        $stats = $this->clientService->getClientStats($client->id);

        // Get client's current dossier
        $currentDossier = Dossier::where('client_id', $client->id)
            ->with(['client', 'package', 'documents', 'assignedTo'])
            ->latest()
            ->first();

        if (!$currentDossier) {
            return inertia('Dashboard/Roles/Client', [
                'stats' => [
                    'currentDossier' => null,
                    'uploadedDocuments' => 0,
                    'missingDocuments' => 0,
                    'nextStep' => 'Aucun dossier actif',
                ],
                'recentActivity' => [],
                'pendingActions' => [],
                'upcomingAppointments' => [],
            ]);
        }

        // Get progress using service
        $progress = $this->dossierService->getProgress($currentDossier->id);

        // Calculate uploaded and missing documents
        $uploadedDocuments = $progress['uploaded'];
        $missingDocuments = $progress['required'] - $progress['uploaded'];

        // Get next step based on dossier status
        $nextStep = $this->getNextStep($currentDossier);

        // Get assigned agent
        $agent = $currentDossier->assignedTo ?? null;

        // Recent activities for this client's dossier
        $recentActivity = Activity::where('subject_type', Dossier::class)
            ->where('subject_id', $currentDossier->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $this->formatActivityTitle($activity->description),
                    'description' => $activity->description,
                    'date' => $activity->created_at->diffForHumans(),
                    'type' => $this->getActivityType($activity->description),
                ];
            });

        // Pending actions (documents to upload)
        $pendingActions = $currentDossier->documents()
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name ?? 'Document',
                    'description' => $doc->description ?? 'Document requis pour votre dossier',
                    'uploaded' => !empty($doc->path),
                ];
            });

        // Upcoming appointments (mock data for now - integrate with appointments system)
        $upcomingAppointments = [];

        return inertia('Dashboard/Roles/Client', [
            'stats' => [
                'currentDossier' => [
                    'id' => $currentDossier->id,
                    'reference' => $currentDossier->reference,
                    'status' => $currentDossier->status,
                    'progress' => $progress['percentage'],
                    'agent' => $agent ? [
                        'id' => $agent->id,
                        'name' => $agent->name,
                    ] : null,
                    'package' => $currentDossier->package ? [
                        'id' => $currentDossier->package->id,
                        'name' => $currentDossier->package->name,
                    ] : null,
                ],
                'uploadedDocuments' => $uploadedDocuments,
                'missingDocuments' => $missingDocuments,
                'nextStep' => $nextStep,
            ],
            'recentActivity' => $recentActivity,
            'pendingActions' => $pendingActions,
            'upcomingAppointments' => $upcomingAppointments,
        ]);
    }

    /**
     * Staff dashboard with general stats
     */
    private function staffDashboard()
    {
        $dossierQuery = Dossier::query();
        $documentQuery = Document::query();

        // Stats
        $totalDossiers = $dossierQuery->count();
        $activeDossiers = (clone $dossierQuery)->whereHas('documents')->count();
        $totalDocuments = $documentQuery->count();

        // Pending signatures
        $pendingSignatures = Activity::query()
            ->where('description', 'Signature request sent')
            ->count();

        // Recent dossiers
        $recentDossiers = (clone $dossierQuery)
            ->with(['client'])
            ->withCount('documents')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($dossier) {
                $status = $dossier->status ?? ($dossier->documents_count > 0 ? 'in_progress' : 'draft');
                return [
                    'id' => $dossier->id,
                    'reference' => $dossier->reference,
                    'title' => $dossier->title,
                    'status' => $status,
                    'client' => $dossier->client ? [
                        'id' => $dossier->client->id,
                        'name' => $dossier->client->nom . ' ' . $dossier->client->prenom ?? 
                                  $dossier->client->first_name . ' ' . $dossier->client->last_name ?? 
                                  'N/A',
                    ] : null,
                ];
            });

        // Recent activities
        $recentActivities = Activity::query()
            ->latest()
            ->take(10)
            ->get(['id', 'description', 'created_at'])
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at?->toDateTimeString(),
                ];
            });

        return inertia('Dashboard', [
            'stats' => [
                'totalDossiers' => $totalDossiers,
                'activeDossiers' => $activeDossiers,
                'totalDocuments' => $totalDocuments,
                'pendingSignatures' => $pendingSignatures,
            ],
            'recentDossiers' => $recentDossiers,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Calculate dossier progress percentage
     */
    private function calculateProgress(Dossier $dossier): int
    {
        $statusProgress = [
            'draft' => 10,
            'pending' => 30,
            'in_progress' => 60,
            'approved' => 90,
            'completed' => 100,
            'rejected' => 0,
        ];

        $baseProgress = $statusProgress[$dossier->status] ?? 10;

        // Add progress based on documents
        $totalDocuments = $dossier->documents()->count();
        if ($totalDocuments > 0) {
            $uploadedDocuments = $dossier->documents()->where('path', '!=', '')->whereNotNull('path')->count();
            $documentProgress = ($uploadedDocuments / $totalDocuments) * 20;
            $baseProgress = min(100, $baseProgress + $documentProgress);
        }

        return (int) $baseProgress;
    }

    /**
     * Get next step based on dossier status
     */
    private function getNextStep(Dossier $dossier): string
    {
        $nextSteps = [
            'draft' => 'Télécharger les documents requis',
            'pending' => 'En attente de vérification',
            'in_progress' => 'Traitement en cours',
            'approved' => 'Finalisation du dossier',
            'completed' => 'Dossier finalisé',
            'rejected' => 'Dossier rejeté - Voir les détails',
        ];

        return $nextSteps[$dossier->status] ?? 'En cours';
    }

    /**
     * Format activity title for better readability
     */
    private function formatActivityTitle(string $description): string
    {
        // Simple formatting - can be enhanced
        return ucfirst($description);
    }

    /**
     * Get activity type for styling
     */
    private function getActivityType(string $description): string
    {
        $description = strtolower($description);

        if (str_contains($description, 'approved') || str_contains($description, 'completed') || str_contains($description, 'uploaded')) {
            return 'success';
        }

        if (str_contains($description, 'rejected') || str_contains($description, 'error') || str_contains($description, 'missing')) {
            return 'warning';
        }

        return 'info';
    }
}