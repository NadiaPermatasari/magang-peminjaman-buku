<?php

namespace App\Policies;

use App\Models\BookCopy;
use App\Models\User;

class BookCopyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('book-copies.view');
    }

    public function view(User $user, BookCopy $bookCopy): bool
    {
        return $user->can('book-copies.view');
    }

    public function create(User $user): bool
    {
        return $user->can('book-copies.create');
    }

    public function update(User $user, BookCopy $bookCopy): bool
    {
        return $user->can('book-copies.update');
    }

    public function delete(User $user, BookCopy $bookCopy): bool
    {
        return $user->can('book-copies.delete');
    }
}
