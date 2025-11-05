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
        // SuperAdmin or Agent or permission 'manage documents'
        if ($user->hasRole('SuperAdmin') || $user->hasRole('Agent')) {
            return true;
        }

        if ($user->can('manage documents')) {
            return true;
        }

        // Additional logic: allow uploader if they are the client's owner? (placeholder)
        return false;
    }

    public function view(User $user, Document $document): bool
    {
        // Allow SuperAdmin, agents or users with 'view documents' permission
        if ($user->hasRole('SuperAdmin') || $user->hasRole('Agent')) {
            return true;
        }

        if ($user->can('view documents')) {
            return true;
        }

        // Allow uploader to view their own uploaded document
        if ($document->uploaded_by && $user->id === $document->uploaded_by) {
            return true;
        }

        return false;
    }
}
