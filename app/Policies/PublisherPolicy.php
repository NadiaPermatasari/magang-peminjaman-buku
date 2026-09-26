<?php

namespace App\Policies;

use App\Models\Publisher;
use App\Models\User;

class PublisherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('publishers.view');
    }

    public function view(User $user, Publisher $publisher): bool
    {
        return $user->can('publishers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('publishers.create');
    }

    public function update(User $user, Publisher $publisher): bool
    {
        return $user->can('publishers.update');
    }

    public function delete(User $user, Publisher $publisher): bool
    {
        return $user->can('publishers.delete');
    }
}
