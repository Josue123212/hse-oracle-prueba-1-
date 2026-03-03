<?php

namespace App\Policies;

use App\Models\ActivityExecution;
use App\Models\User;

class ActivityExecutionPolicy
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
    public function view(User $user, ActivityExecution $activityExecution): bool
    {
        return $user->isAdmin() || $user->isEncargado() || $user->isLector() || $user->isAlumno();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Executions are typically system-generated, but Encargado might need to add one-offs.
        return $user->isAdmin() || $user->isEncargado();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActivityExecution $activityExecution): bool
    {
        return $user->isAdmin() || $user->isEncargado();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ActivityExecution $activityExecution): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ActivityExecution $activityExecution): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ActivityExecution $activityExecution): bool
    {
        return $user->isAdmin();
    }
}
