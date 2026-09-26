<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorRequest;
use App\Models\Author;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Author::class);

        $search = trim((string) $request->query('search', ''));

        $authors = Author::query()
            ->withCount('books')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('authors.index', ['authors' => $authors]);
    }

    public function create()
    {
        $this->authorize('create', Author::class);

        return view('authors.create', ['author' => new Author]);
    }

    public function store(AuthorRequest $request): RedirectResponse
    {
        $author = Author::create($request->validated());

        Activity::log('AUTHOR_CREATED', "Created author {$author->name}", $author);

        return redirect()->route('authors.index')->with('success', "Penulis {$author->name} berhasil ditambahkan.");
    }

    public function edit(Author $author)
    {
        $this->authorize('update', $author);

        return view('authors.edit', ['author' => $author]);
    }

    public function update(AuthorRequest $request, Author $author): RedirectResponse
    {
        $author->update($request->validated());

        Activity::log('AUTHOR_UPDATED', "Updated author {$author->name}", $author);

        return redirect()->route('authors.index')->with('success', "Penulis {$author->name} berhasil diperbarui.");
    }

    public function destroy(Author $author): RedirectResponse
    {
        $this->authorize('delete', $author);

        if ($author->books()->exists()) {
            return back()->with('error', 'Penulis tidak dapat dihapus karena masih memiliki buku.');
        }

        Activity::log('AUTHOR_DELETED', "Deleted author {$author->name}");

        $author->delete();

        return redirect()->route('authors.index')->with('success', "Penulis {$author->name} berhasil dihapus.");
    }
}
