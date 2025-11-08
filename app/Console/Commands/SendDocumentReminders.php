<?php

namespace App\Console\Commands;

use App\Models\Dossier;
use App\Notifications\DocumentRequiredNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendDocumentReminders extends Command
{
    protected $signature = 'documents:send-reminders
                            {--days=7 : Number of days since last status change}
                            {--dry-run : Show what would be sent without sending}';

    protected $description = 'Send reminders for dossiers waiting for documents (J+7 after status change)';

    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        
        $this->info("🔍 Recherche des dossiers en attente de documents depuis {$days} jours...");

        // Find dossiers in pending/in_progress status for more than X days without recent activity
        $dossiers = Dossier::with(['client', 'documents', 'package'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('updated_at', '<=', now()->subDays($days))
            ->whereDoesntHave('documents', function ($query) use ($days) {
                // No documents uploaded in the last X days
                $query->where('created_at', '>=', now()->subDays($days));
            })
            ->get();

        if ($dossiers->isEmpty()) {
            $this->info('✅ Aucun dossier nécessitant un rappel.');
            return Command::SUCCESS;
        }

        $this->warn("📋 {$dossiers->count()} dossier(s) trouvé(s)");
        $count = 0;

        foreach ($dossiers as $dossier) {
            // Determine missing documents based on package requirements
            $requiredDocs = $this->getRequiredDocuments($dossier);
            $existingDocs = $dossier->documents->pluck('type')->unique()->toArray();
            $missingDocs = array_diff($requiredDocs, $existingDocs);

            if (empty($missingDocs)) {
                continue; // All documents are present
            }

            $daysSinceUpdate = Carbon::parse($dossier->updated_at)->diffInDays(now());

            if ($dryRun) {
                $this->line("  📧 [DRY-RUN] Rappel pour dossier #{$dossier->id} - Client: {$dossier->client->name}");
                $this->line("     Documents manquants: " . implode(', ', $missingDocs));
                $this->line("     Jours depuis dernière MAJ: {$daysSinceUpdate}");
            } else {
                try {
                    $dossier->client->notify(new DocumentRequiredNotification($dossier, $missingDocs));
                    
                    // Log activity
                    activity()
                        ->performedOn($dossier)
                        ->causedBy(null) // System action
                        ->withProperties([
                            'missing_documents' => $missingDocs,
                            'days_since_update' => $daysSinceUpdate
                        ])
                        ->log('Rappel de documents envoyé automatiquement');

                    $this->info("  ✅ Rappel envoyé: Dossier #{$dossier->id} - {$dossier->client->name}");
                    $count++;
                    
                } catch (\Exception $e) {
                    $this->error("  ❌ Erreur pour dossier #{$dossier->id}: {$e->getMessage()}");
                    Log::error("Erreur rappel documents dossier #{$dossier->id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        if ($dryRun) {
            $this->info("\n✨ Mode DRY-RUN: {$dossiers->count()} rappel(s) seraient envoyé(s)");
        } else {
            $this->info("\n✨ Terminé: {$count} rappel(s) envoyé(s) avec succès");
        }

        return Command::SUCCESS;
    }

    /**
     * Get required documents based on package/dossier type
     */
    private function getRequiredDocuments(Dossier $dossier): array
    {
        // Base documents for all dossiers
        $required = [
            'passport',
            'photo',
            'birth_certificate',
        ];

        // Add package-specific requirements
        if ($dossier->package) {
            $packageName = strtolower($dossier->package->name);
            
            if (str_contains($packageName, 'etude') || str_contains($packageName, 'student')) {
                $required[] = 'diploma';
                $required[] = 'transcript';
                $required[] = 'admission_letter';
            }
            
            if (str_contains($packageName, 'travail') || str_contains($packageName, 'work')) {
                $required[] = 'employment_contract';
                $required[] = 'cv';
                $required[] = 'work_permit';
            }
            
            if (str_contains($packageName, 'famille') || str_contains($packageName, 'family')) {
                $required[] = 'marriage_certificate';
                $required[] = 'family_book';
            }
        }

        return $required;
    }
}
