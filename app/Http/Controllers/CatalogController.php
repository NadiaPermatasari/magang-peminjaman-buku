<?php

namespace App\Http\Controllers;

use App\Enums\BookCopyStatus;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Public-facing catalog browsing (spec §41 "Semua User > Katalog"). Read
 * only, available to every authenticated user regardless of permission —
 * not gated by BookPolicy (that guards the Master Data admin screen).
 */
class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');

        $books = Book::query()
            ->where('is_active', true)
            ->with('category')
            ->withCount(['copies as available_count' => fn ($q) => $q->where('status', BookCopyStatus::AVAILABLE)])
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('isbn', 'like', "%{$search}%"))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Book $book)
    {
        abort_unless($book->is_active, 404);

        $book->load(['category', 'rack']);

        return view('catalog.show', [
            'book' => $book,
            'availableCopies' => $book->copies()->where('status', BookCopyStatus::AVAILABLE)->count(),
        ]);
    }
}
