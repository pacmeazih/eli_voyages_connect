<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Dossier;

class ProgressController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Pour les clients, récupérer leur dossier
        if ($user->hasRole('Client')) {
            $client = Client::where('email', $user->email)->first();
            
            if (!$client) {
                return Inertia::render('Progress', [
                    'dossier' => null,
                    'activities' => []
                ]);
            }
            
            // Récupérer le dossier actif du client
            $dossier = Dossier::with(['package', 'activities.causer'])
                ->where('client_id', $client->id)
                ->whereIn('status', ['draft', 'submitted', 'in_progress', 'pending_documents', 'under_review', 'approved'])
                ->latest()
                ->first();
            
            if (!$dossier) {
                // Si pas de dossier actif, récupérer le dernier dossier
                $dossier = Dossier::with(['package', 'activities.causer'])
                    ->where('client_id', $client->id)
                    ->latest()
                    ->first();
            }
            
            $activities = $dossier 
                ? $dossier->activities()->with('causer')->latest()->get()
                : collect();
            
            return Inertia::render('Progress', [
                'dossier' => $dossier,
                'activities' => $activities
            ]);
        }
        
        // Pour les staff, rediriger vers la liste des dossiers
        return redirect()->route('dossiers.index');
    }
}
