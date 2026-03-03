<?php

namespace App\Policies;

use App\Models\TrainingAttendance;
use App\Models\User;

class TrainingAttendancePolicy
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
    public function view(User $user, TrainingAttendance $trainingAttendance): bool
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
    public function update(User $user, TrainingAttendance $trainingAttendance): bool
    {
        return $user->isAdmin() || $user->isEncargado();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TrainingAttendance $trainingAttendance): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TrainingAttendance $trainingAttendance): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TrainingAttendance $trainingAttendance): bool
    {
        return $user->isAdmin();
    }
}
