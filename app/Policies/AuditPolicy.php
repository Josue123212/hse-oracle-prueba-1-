<?php

namespace App\Policies;

use App\Models\Audit;
use App\Models\User;

class AuditPolicy
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
    public function view(User $user, \Illuminate\Database\Eloquent\Model $model): bool
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
    public function update(User $user, \Illuminate\Database\Eloquent\Model $model): bool
    {
        return $user->isAdmin() || $user->isEncargado();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, \Illuminate\Database\Eloquent\Model $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, \Illuminate\Database\Eloquent\Model $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, \Illuminate\Database\Eloquent\Model $model): bool
    {
        return $user->isAdmin();
    }
}
