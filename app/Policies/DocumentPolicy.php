<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Dossier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function upload(User $user, Dossier $dossier): bool
    {
        // SuperAdmin, Admin, Agent, Consultant can upload
        if ($user->hasRole(['SuperAdmin', 'Admin', 'Agent', 'Consultant'])) {
            return true;
        }

        if ($user->can('manage documents')) {
            return true;
        }

        // Allow clients to upload to their own dossier
        if ($user->hasRole('Client')) {
            // Check if user has a client record with matching email
            $client = \App\Models\Client::where('email', $user->email)->first();
            if ($client && $dossier->client_id === $client->id) {
                return true;
            }
        }

        return false;
    }

    public function view(User $user, Document $document): bool
    {
        // Allow SuperAdmin, Admin, Agent, Consultant
        if ($user->hasRole(['SuperAdmin', 'Admin', 'Agent', 'Consultant'])) {
            return true;
        }

        if ($user->can('view documents')) {
            return true;
        }

        // Allow clients to view documents from their own dossier
        if ($user->hasRole('Client')) {
            $client = \App\Models\Client::where('email', $user->email)->first();
            if ($client && $document->dossier && $document->dossier->client_id === $client->id) {
                return true;
            }
        }

        // Allow uploader to view their own uploaded document
        if ($document->uploaded_by && $user->id === $document->uploaded_by) {
            return true;
        }

        return false;
    }

    public function download(User $user, Document $document): bool
    {
        // Same logic as view
        return $this->view($user, $document);
    }
}
