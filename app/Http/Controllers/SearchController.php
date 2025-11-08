<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Global search across multiple entities
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([
                'query' => $query,
                'results' => [
                    'dossiers' => [],
                    'clients' => [],
                    'documents' => [],
                    'users' => [],
                ],
                'total' => 0,
            ]);
        }

        $user = auth()->user();
        $isClient = $user->hasRole('Client');

        // Search Dossiers
        $dossiersQuery = Dossier::with(['client', 'package'])
            ->where(function ($q) use ($query) {
                $q->where('reference', 'like', "%{$query}%")
                    ->orWhere('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });

        // If user is a client, only show their dossiers
        if ($isClient) {
            $client = Client::where('email', $user->email)->first();
            if ($client) {
                $dossiersQuery->where('client_id', $client->id);
            }
        }

        $dossiers = $dossiersQuery->limit($limit)->get()->map(function ($dossier) {
            return [
                'id' => $dossier->id,
                'type' => 'dossier',
                'title' => $dossier->reference,
                'subtitle' => $dossier->title,
                'description' => $dossier->client->name ?? '',
                'status' => $dossier->status,
                'url' => route('dossiers.show', $dossier->id),
                'icon' => 'folder',
            ];
        });

        // Search Clients (only for non-client users)
        $clients = collect([]);
        if (!$isClient && $user->can('view clients')) {
            $clients = Client::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('passport_number', 'like', "%{$query}%");
            })
                ->limit($limit)
                ->get()
                ->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'type' => 'client',
                        'title' => $client->name,
                        'subtitle' => $client->email,
                        'description' => $client->phone ?? '',
                        'status' => null,
                        'url' => route('dossiers.index', ['client' => $client->id]),
                        'icon' => 'user',
                    ];
                });
        }

        // Search Documents
        $documentsQuery = Document::with(['dossier'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('original_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });

        // If user is a client, only show their documents
        if ($isClient) {
            $client = Client::where('email', $user->email)->first();
            if ($client) {
                $documentsQuery->whereHas('dossier', function ($q) use ($client) {
                    $q->where('client_id', $client->id);
                });
            }
        }

        $documents = $documentsQuery->limit($limit)->get()->map(function ($document) {
            return [
                'id' => $document->id,
                'type' => 'document',
                'title' => $document->name,
                'subtitle' => $document->original_name,
                'description' => $document->dossier->reference ?? '',
                'status' => $document->status,
                'url' => route('documents.show', $document->id),
                'icon' => 'document',
            ];
        });

        // Search Users (only for admins)
        $users = collect([]);
        if (!$isClient && $user->can('manage users')) {
            $users = User::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'type' => 'user',
                        'title' => $user->name,
                        'subtitle' => $user->email,
                        'description' => $user->roles->pluck('name')->join(', '),
                        'status' => null,
                        'url' => '#',
                        'icon' => 'user-circle',
                    ];
                });
        }

        $allResults = [
            'dossiers' => $dossiers->toArray(),
            'clients' => $clients->toArray(),
            'documents' => $documents->toArray(),
            'users' => $users->toArray(),
        ];

        $total = $dossiers->count() + $clients->count() + $documents->count() + $users->count();

        return response()->json([
            'query' => $query,
            'results' => $allResults,
            'total' => $total,
        ]);
    }
}
