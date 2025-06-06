<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        if ($project->privacy === 'public') {
            return true;
        }

        if ($project->users->contains($user)) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return true;
        }

        return false;
    }

    public function useFiles(User $user, Project $project): bool
    {
        if ($project->users->contains($user)) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return true;
        }

        return false;
    }

    public function addUser(User $user, Project $project): bool
    {
        if ($project->users->contains($user) && $project->users->where('user_id', $user->id)->where('role', ['admin', 'owner'])) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return true;
        }

        return false;
    }

    public function removeUser(User $user, Project $project): bool
    {
        if ($project->users->contains($user) && $project->users->where('user_id', $user->id)->where('role', ['admin', 'owner'])) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return true;
        }

        return false;
    }

    public function updateUser(User $user, Project $project): bool
    {
        if ($project->users->contains($user) && $project->users->where('user_id', $user->id)->where('role', ['admin', 'owner'])) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        if ($project->users->contains($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($project->users->contains($user) && $project->users->where('user_id', $user->id)->where('role', ['admin', 'owner'])) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        if ($project->users->contains($user) && $project->users->where('user_id', $user->id)->where('role', ['owner'])) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    public function storeFiles(User $user, Project $project): bool
    {
        if ($project->users->contains($user))
        {
            return true;
        }

        return false;
    }
}
