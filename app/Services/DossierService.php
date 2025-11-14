<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\Client;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\LogActivity;

class DossierService
{
    /**
     * Create a new dossier for a client
     */
    public function createDossier(int $clientId, int $packageId, array $additionalData = []): Dossier
    {
        return DB::transaction(function () use ($clientId, $packageId, $additionalData) {
            $dossier = Dossier::create([
                'client_id' => $clientId,
                'package_id' => $packageId,
                'statut' => 'documents_requis',
                'date_creation' => now(),
                ...$additionalData
            ]);

            // Log activity
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties(['status' => 'created'])
                ->log('Dossier créé');

            return $dossier;
        });
    }

    /**
     * Update dossier status
     */
    public function updateStatus(int $dossierId, string $newStatus, ?string $comment = null): bool
    {
        $dossier = Dossier::find($dossierId);

        if (!$dossier) {
            return false;
        }

        $oldStatus = $dossier->statut;
        $dossier->statut = $newStatus;
        $dossier->save();

        // Log status change
        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->withProperties([
                'status' => $newStatus,
                'old_status' => $oldStatus,
                'comment' => $comment
            ])
            ->log("Statut changé de {$oldStatus} à {$newStatus}");

        return true;
    }

    /**
     * Get dossier with all relations
     */
    public function getDossierWithRelations(int $dossierId): ?Dossier
    {
        return Dossier::with([
            'client',
            'package.documents',
            'documents',
            'activities.causer'
        ])->find($dossierId);
    }

    /**
     * Get dossier progress percentage
     */
    public function getProgress(int $dossierId): array
    {
        $dossier = Dossier::with(['package.documents', 'documents'])->find($dossierId);

        if (!$dossier) {
            return ['percentage' => 0, 'uploaded' => 0, 'required' => 0];
        }

        // Get required documents from package
        $requiredCount = $dossier->package ? $dossier->package->documents()->where('requis', true)->count() : 5;
        $uploadedCount = $dossier->documents()->count();

        return [
            'percentage' => $requiredCount > 0 ? round(($uploadedCount / $requiredCount) * 100) : 0,
            'uploaded' => $uploadedCount,
            'required' => $requiredCount,
        ];
    }

    /**
     * Check if all required documents are uploaded
     */
    public function hasAllRequiredDocuments(int $dossierId): bool
    {
        $progress = $this->getProgress($dossierId);
        return $progress['percentage'] === 100;
    }

    /**
     * Get dossiers by status
     */
    public function getDossiersByStatus(string $status, int $limit = 10)
    {
        return Dossier::with(['client', 'package'])
            ->where('statut', $status)
            ->latest()
            ->paginate($limit);
    }

    /**
     * Archive completed dossier
     */
    public function archiveDossier(int $dossierId): bool
    {
        return $this->updateStatus($dossierId, 'archive', 'Dossier archivé');
    }
}
