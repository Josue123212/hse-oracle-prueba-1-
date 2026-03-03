<?php

namespace App\Policies;

use App\Models\Inspection;
use App\Models\User;

class InspectionPolicy
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
    public function view(User $user, Inspection $inspection): bool
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
    public function update(User $user, Inspection $inspection): bool
    {
        return $user->isAdmin() || $user->isEncargado();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Inspection $inspection): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Inspection $inspection): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Inspection $inspection): bool
    {
        return $user->isAdmin();
    }
}
