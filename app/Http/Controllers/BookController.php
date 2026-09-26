<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Rack;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Book::class);

        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');

        $books = Book::query()
            ->with(['category', 'publisher', 'authors'])
            ->withCount('copies')
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('isbn', 'like', "%{$search}%"))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->latest()
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Book::class);

        return view('books.create', [
            'book' => new Book,
            'categories' => Category::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'racks' => Rack::orderBy('code')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['cover', 'authors']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book = Book::create($data);
        $book->authors()->sync($request->input('authors', []));

        Activity::log('BOOK_CREATED', "Created book {$book->title}", $book);

        return redirect()->route('books.index')->with('success', "Buku {$book->title} berhasil ditambahkan.");
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        return view('books.edit', [
            'book' => $book->load('authors'),
            'categories' => Category::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'racks' => Rack::orderBy('code')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $data = $request->safe()->except(['cover', 'authors']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('cover')) {
            if ($book->cover_path) {
                Storage::disk('public')->delete($book->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);
        $book->authors()->sync($request->input('authors', []));

        Activity::log('BOOK_UPDATED', "Updated book {$book->title}", $book);

        return redirect()->route('books.index')->with('success', "Buku {$book->title} berhasil diperbarui.");
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        if ($book->copies()->exists()) {
            return back()->with('error', 'Buku tidak dapat dihapus karena masih memiliki eksemplar. Hapus eksemplarnya terlebih dahulu.');
        }

        Activity::log('BOOK_DELETED', "Deleted book {$book->title}");

        $book->delete();

        return redirect()->route('books.index')->with('success', "Buku {$book->title} berhasil dihapus.");
    }
}
