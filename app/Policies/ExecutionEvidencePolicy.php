<?php

namespace App\Policies;

use App\Models\ExecutionEvidence;
use App\Models\User;

class ExecutionEvidencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEncargado() || $user->isLector() || $user->isAlumno();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExecutionEvidence $executionEvidence): bool
    {
        return $user->isAdmin() || $user->isEncargado() || $user->isLector() || $user->isAlumno();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEncargado();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExecutionEvidence $executionEvidence): bool
    {
        // WORM Compliance: Evidence cannot be modified once created.
        // If an error occurred, the evidence should be deleted (by Admin) and re-uploaded.
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExecutionEvidence $executionEvidence): bool
    {
        // Deletion of evidence is sensitive. Only Admin should delete.
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExecutionEvidence $executionEvidence): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExecutionEvidence $executionEvidence): bool
    {
        return $user->isAdmin();
    }
}
