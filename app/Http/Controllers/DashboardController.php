<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Document;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Base dossier/document queries with role-based visibility if needed
        $dossierQuery = Dossier::query();
        $documentQuery = Document::query();

        // If you have client-facing users, restrict their view (adjust as per your auth model)
        if (auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('Client')) {
            // Assuming client users should only see their own dossiers
            $dossierQuery->where('client_id', auth()->id());
            $documentQuery->whereHas('dossier', function ($q) {
                $q->where('client_id', auth()->id());
            });
        }

        // Stats
        $totalDossiers = (clone $dossierQuery)->count();
        // Active dossiers heuristic: dossiers with at least one document
        $activeDossiers = (clone $dossierQuery)->whereHas('documents')->count();
        $totalDocuments = (clone $documentQuery)->count();

        // Pending signatures (basic approximation based on activity logs)
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
                // Use the status field if it exists, otherwise derive it
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
}