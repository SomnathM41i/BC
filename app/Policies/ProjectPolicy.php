<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isOwner();
    }

    public function manageFinance(User $user, Project $project): bool
    {
        return $user->isOwner();
    }

    public function close(User $user, Project $project): bool
    {
        return $user->isOwner();
    }
}
