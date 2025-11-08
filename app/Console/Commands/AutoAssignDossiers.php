<?php

namespace App\Console\Commands;

use App\Models\Dossier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoAssignDossiers extends Command
{
    protected $signature = 'dossiers:auto-assign
                            {--strategy=workload : Assignment strategy (round-robin or workload)}';

    protected $description = 'Automatically assign unassigned dossiers to agents';

    public function handle()
    {
        $strategy = $this->option('strategy');
        $this->info("🔄 Assignation automatique des dossiers (stratégie: {$strategy})...");

        // Find unassigned dossiers (agent_id is null or 0)
        $unassignedDossiers = Dossier::with('client')
            ->whereNull('agent_id')
            ->orWhere('agent_id', 0)
            ->whereNotIn('status', ['completed', 'rejected', 'archived'])
            ->get();

        if ($unassignedDossiers->isEmpty()) {
            $this->info('✅ Aucun dossier à assigner.');
            return Command::SUCCESS;
        }

        // Get available agents (users with Agent or Admin role)
        $agents = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Agent', 'Admin']);
        })->get();

        if ($agents->isEmpty()) {
            $this->error('❌ Aucun agent disponible pour l\'assignation.');
            return Command::FAILURE;
        }

        $this->warn("📋 {$unassignedDossiers->count()} dossier(s) non assigné(s) trouvé(s)");
        
        $count = 0;
        
        if ($strategy === 'round-robin') {
            $count = $this->roundRobinAssignment($unassignedDossiers, $agents);
        } elseif ($strategy === 'workload') {
            $count = $this->workloadBasedAssignment($unassignedDossiers, $agents);
        } else {
            $this->error("❌ Stratégie inconnue: {$strategy}");
            return Command::FAILURE;
        }

        $this->info("\n✨ {$count} dossier(s) assigné(s) avec succès.");
        return Command::SUCCESS;
    }

    /**
     * Round-robin assignment: distribute evenly
     */
    private function roundRobinAssignment($dossiers, $agents)
    {
        $count = 0;
        $agentIndex = 0;

        foreach ($dossiers as $dossier) {
            try {
                $agent = $agents[$agentIndex % $agents->count()];
                
                $dossier->update(['agent_id' => $agent->id]);
                
                activity()
                    ->performedOn($dossier)
                    ->causedBy(null)
                    ->withProperties(['strategy' => 'round-robin'])
                    ->log("Dossier auto-assigné à {$agent->name}");
                
                $this->line("  ✅ Dossier #{$dossier->id} → {$agent->name}");
                
                $count++;
                $agentIndex++;
            } catch (\Exception $e) {
                Log::error("Erreur assignation dossier #{$dossier->id}: {$e->getMessage()}");
                $this->error("  ❌ Erreur pour le dossier #{$dossier->id}");
            }
        }

        return $count;
    }

    /**
     * Workload-based assignment: assign to agent with least dossiers
     */
    private function workloadBasedAssignment($dossiers, $agents)
    {
        $count = 0;

        foreach ($dossiers as $dossier) {
            try {
                // Get agent with lowest active dossier count
                $agent = $agents->sortBy(function ($agent) {
                    return $agent->dossiers()
                        ->whereNotIn('status', ['completed', 'rejected', 'archived'])
                        ->count();
                })->first();

                $dossier->update(['agent_id' => $agent->id]);
                
                activity()
                    ->performedOn($dossier)
                    ->causedBy(null)
                    ->withProperties(['strategy' => 'workload'])
                    ->log("Dossier auto-assigné à {$agent->name}");
                
                $this->line("  ✅ Dossier #{$dossier->id} → {$agent->name}");
                
                $count++;
            } catch (\Exception $e) {
                Log::error("Erreur assignation dossier #{$dossier->id}: {$e->getMessage()}");
                $this->error("  ❌ Erreur pour le dossier #{$dossier->id}");
            }
        }

        return $count;
    }
}
