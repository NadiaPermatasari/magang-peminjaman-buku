<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Gates the Master Data > Buku admin screen. The public-facing "Katalog"
     * (spec §41, all authenticated users) is served by CatalogController and
     * does not go through this policy.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('books.view');
    }

    public function view(User $user, Book $book): bool
    {
        return $user->can('books.view');
    }

    public function create(User $user): bool
    {
        return $user->can('books.create');
    }

    public function update(User $user, Book $book): bool
    {
        return $user->can('books.update');
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->can('books.delete');
    }
}
