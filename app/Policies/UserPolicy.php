<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('users.update');
    }

    /**
     * "Delete" here means disable (spec §4 only grants users.disable, no
     * hard-delete permission exists for user accounts).
     */
    public function delete(User $user, User $target): bool
    {
        return $user->can('users.disable');
    }
}
