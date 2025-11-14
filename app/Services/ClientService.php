<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use App\Models\Dossier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientService
{
    /**
     * Create a new client with user account
     */
    public function createClientWithUser(array $clientData, array $userData): Client
    {
        return DB::transaction(function () use ($clientData, $userData) {
            // Create User account
            $user = User::create([
                'name' => $userData['name'] ?? $clientData['prenom'] . ' ' . $clientData['nom'],
                'email' => $clientData['email'],
                'password' => Hash::make($userData['password']),
            ]);

            // Assign Client role
            $user->assignRole('Client');

            // Create Client record
            $client = Client::create($clientData);

            return $client;
        });
    }

    /**
     * Find client by client_code or email
     */
    public function findByClientCodeOrEmail(string $identifier): ?Client
    {
        if (preg_match('/^ELV-\d{4}-\d{3}$/', $identifier)) {
            return Client::where('client_code', $identifier)->first();
        }

        return Client::where('email', $identifier)->first();
    }

    /**
     * Get client with active dossier
     */
    public function getClientWithActiveDossier(int $clientId): ?Client
    {
        return Client::with(['dossiers' => function ($query) {
            $query->whereIn('statut', ['en_cours', 'documents_requis', 'verification'])
                  ->latest();
        }])->find($clientId);
    }

    /**
     * Get client statistics
     */
    public function getClientStats(int $clientId): array
    {
        $client = Client::with('dossiers.documents', 'dossiers.package.documents')->find($clientId);

        if (!$client) {
            return [];
        }

        return [
            'total_dossiers' => $client->dossiers->count(),
            'active_dossiers' => $client->dossiers->whereIn('statut', ['en_cours', 'documents_requis'])->count(),
            'completed_dossiers' => $client->dossiers->where('statut', 'termine')->count(),
            'pending_documents' => $this->getPendingDocumentsCount($client),
            'total_documents' => $client->dossiers->sum(fn($d) => $d->documents->count()),
        ];
    }

    /**
     * Get pending documents count for client
     */
    protected function getPendingDocumentsCount(Client $client): int
    {
        $count = 0;
        foreach ($client->dossiers as $dossier) {
            if ($dossier->package) {
                $requiredCount = $dossier->package->documents()->where('requis', true)->count();
                $uploadedCount = $dossier->documents()->count();
                $count += max(0, $requiredCount - $uploadedCount);
            }
        }
        return $count;
    }

    /**
     * Update client profile
     */
    public function updateProfile(int $clientId, array $data): bool
    {
        $client = Client::find($clientId);
        
        if (!$client) {
            return false;
        }

        return $client->update($data);
    }

    /**
     * Check if client code exists
     */
    public function clientCodeExists(string $clientCode): bool
    {
        return Client::where('client_code', $clientCode)->exists();
    }
}
