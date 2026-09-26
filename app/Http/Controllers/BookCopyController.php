<?php

namespace App\Http\Controllers;

use App\Enums\BookCondition;
use App\Enums\BookCopyStatus;
use App\Http\Requests\StoreBookCopyRequest;
use App\Http\Requests\UpdateBookCopyRequest;
use App\Models\Book;
use App\Models\BookCopy;
use App\Support\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Route parameters are named `$book_copy` (snake_case) to match Laravel's
 * default implicit-binding parameter name for the hyphenated `book-copies`
 * resource route — a camelCase `$bookCopy` here would silently fail to bind.
 */
class BookCopyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', BookCopy::class);

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $copies = BookCopy::query()
            ->with('book')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                    ->orWhere('inventory_code', 'like', "%{$search}%")
                    ->orWhereHas('book', fn ($bq) => $bq->where('title', 'like', "%{$search}%"));
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('book-copies.index', [
            'copies' => $copies,
            'statuses' => BookCopyStatus::cases(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', BookCopy::class);

        return view('book-copies.create', [
            'copy' => new BookCopy(['condition' => BookCondition::GOOD, 'status' => BookCopyStatus::AVAILABLE]),
            'books' => Book::orderBy('title')->get(),
            'conditions' => BookCondition::cases(),
            'statuses' => BookCopyStatus::cases(),
        ]);
    }

    public function store(StoreBookCopyRequest $request): RedirectResponse
    {
        $copy = BookCopy::create($request->validated());

        Activity::log('BOOK_COPY_CREATED', "Created book copy {$copy->barcode}", $copy);

        return redirect()->route('book-copies.index')->with('success', "Eksemplar {$copy->barcode} berhasil ditambahkan.");
    }

    public function edit(BookCopy $book_copy)
    {
        $this->authorize('update', $book_copy);

        return view('book-copies.edit', [
            'copy' => $book_copy,
            'books' => Book::orderBy('title')->get(),
            'conditions' => BookCondition::cases(),
            'statuses' => BookCopyStatus::cases(),
        ]);
    }

    public function update(UpdateBookCopyRequest $request, BookCopy $book_copy): RedirectResponse
    {
        $book_copy->update($request->validated());

        Activity::log('BOOK_COPY_UPDATED', "Updated book copy {$book_copy->barcode}", $book_copy);

        return redirect()->route('book-copies.index')->with('success', "Eksemplar {$book_copy->barcode} berhasil diperbarui.");
    }

    public function destroy(BookCopy $book_copy): RedirectResponse
    {
        $this->authorize('delete', $book_copy);

        if (in_array($book_copy->status, [BookCopyStatus::BORROWED, BookCopyStatus::RESERVED], true)) {
            return back()->with('error', 'Eksemplar tidak dapat dihapus karena sedang dipinjam/direservasi.');
        }

        Activity::log('BOOK_COPY_DELETED', "Deleted book copy {$book_copy->barcode}");

        $book_copy->delete();

        return redirect()->route('book-copies.index')->with('success', "Eksemplar {$book_copy->barcode} berhasil dihapus.");
    }

    /**
     * Fast barcode lookup (spec §8/§61), used by the handover/return scan
     * screens. Barcode is treated as untrusted scanner input and validated
     * like any other user input.
     */
    public function lookup(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BookCopy::class);

        $request->validate(['barcode' => ['required', 'string', 'max:60']]);

        $copy = BookCopy::with('book')->where('barcode', $request->string('barcode'))->first();

        if (! $copy) {
            return response()->json(['message' => 'Barcode tidak ditemukan.'], 404);
        }

        return response()->json([
            'uuid' => $copy->uuid,
            'barcode' => $copy->barcode,
            'status' => $copy->status->value,
            'status_label' => $copy->status->label(),
            'book' => ['title' => $copy->book->title, 'isbn' => $copy->book->isbn],
        ]);
    }
}
