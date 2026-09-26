<?php

namespace App\Policies;

use App\Models\Rack;
use App\Models\User;

class RackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('racks.view');
    }

    public function view(User $user, Rack $rack): bool
    {
        return $user->can('racks.view');
    }

    public function create(User $user): bool
    {
        return $user->can('racks.create');
    }

    public function update(User $user, Rack $rack): bool
    {
        return $user->can('racks.update');
    }

    public function delete(User $user, Rack $rack): bool
    {
        return $user->can('racks.delete');
    }
}
