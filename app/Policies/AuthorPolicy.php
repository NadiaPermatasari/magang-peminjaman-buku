<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('authors.view');
    }

    public function view(User $user, Author $author): bool
    {
        return $user->can('authors.view');
    }

    public function create(User $user): bool
    {
        return $user->can('authors.create');
    }

    public function update(User $user, Author $author): bool
    {
        return $user->can('authors.update');
    }

    public function delete(User $user, Author $author): bool
    {
        return $user->can('authors.delete');
    }
}
