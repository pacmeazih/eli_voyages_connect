<?php

namespace App\Console\Commands;

use App\Models\Dossier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveOldDossiers extends Command
{
    protected $signature = 'dossiers:archive-old
                            {--years=1 : Archive dossiers completed more than X years ago}
                            {--dry-run : Show what would be archived without making changes}';

    protected $description = 'Archive old completed dossiers to reduce active dataset';

    public function handle()
    {
        $years = (int) $this->option('years');
        $dryRun = $this->option('dry-run');
        
        $this->info("🗄️  Recherche de dossiers complétés depuis plus de {$years} an(s)...");
        
        // Find completed dossiers older than X years
        $oldDossiers = Dossier::with('client')
            ->where('status', 'completed')
            ->where('updated_at', '<', now()->subYears($years))
            ->get();

        if ($oldDossiers->isEmpty()) {
            $this->info('✅ Aucun dossier à archiver.');
            return Command::SUCCESS;
        }

        $this->warn("📋 {$oldDossiers->count()} dossier(s) à archiver trouvé(s).");

        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Aucune modification ne sera effectuée.');
            $this->newLine();
            
            foreach ($oldDossiers as $dossier) {
                $completedDate = $dossier->updated_at->format('d/m/Y');
                $this->line("  📄 Dossier #{$dossier->id} - Client: {$dossier->client->name} - Complété: {$completedDate}");
            }
            
            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($oldDossiers as $dossier) {
            try {
                $originalStatus = $dossier->status;
                $dossier->update(['status' => 'archived']);
                
                activity()
                    ->performedOn($dossier)
                    ->causedBy(null)
                    ->withProperties([
                        'original_status' => $originalStatus,
                        'completed_at' => $dossier->updated_at
                    ])
                    ->log('Dossier archivé automatiquement');
                
                $this->line("  ✅ Dossier #{$dossier->id} archivé");
                
                $count++;
            } catch (\Exception $e) {
                Log::error("Erreur archivage dossier #{$dossier->id}: {$e->getMessage()}");
                $this->error("  ❌ Erreur pour le dossier #{$dossier->id}");
            }
        }

        $this->newLine();
        $this->info("✨ {$count} dossier(s) archivé(s) avec succès.");
        
        return Command::SUCCESS;
    }
}
