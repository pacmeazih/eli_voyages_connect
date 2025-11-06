<?php

namespace App\Policies;

use App\Models\Dossier;
use App\Models\User;

class DossierPolicy
{
    /**
     * Determine if the user can view any dossiers.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view dossiers', 'manage users']);
    }

    /**
     * Determine if the user can view the dossier.
     */
    public function view(User $user, Dossier $dossier): bool
    {
        // SuperAdmin and Consultant can view all
        if ($user->hasRole(['SuperAdmin', 'Consultant', 'Agent'])) {
            return true;
        }

        // Client can only view their own dossier
        if ($user->hasRole('Client')) {
            return $dossier->client_id === $user->id;
        }

        // Guarantor can view related dossiers (extend logic as needed)
        if ($user->hasRole('Guarantor')) {
            // TODO: Add guarantor relationship check
            return false;
        }

        return false;
    }

    /**
     * Determine if the user can create dossiers.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create dossiers');
    }

    /**
     * Determine if the user can update the dossier.
     */
    public function update(User $user, Dossier $dossier): bool
    {
        // Only agents and admins can update
        return $user->hasPermissionTo('edit dossiers');
    }

    /**
     * Determine if the user can delete the dossier.
     */
    public function delete(User $user, Dossier $dossier): bool
    {
        return $user->hasPermissionTo('delete dossiers');
    }

    /**
     * Determine if the user can validate the dossier.
     */
    public function validate(User $user, Dossier $dossier): bool
    {
        return $user->hasPermissionTo('validate dossiers');
    }

    /**
     * Determine if the user can approve the dossier.
     */
    public function approve(User $user, Dossier $dossier): bool
    {
        return $user->hasPermissionTo('approve dossiers');
    }
}
