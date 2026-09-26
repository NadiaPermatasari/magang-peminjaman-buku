<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        $search = trim((string) $request->query('search', ''));

        $categories = Category::query()
            ->withCount('books')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        $this->authorize('create', Category::class);

        return view('categories.create', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        Activity::log('CATEGORY_CREATED', "Created category {$category->name}", $category);

        return redirect()->route('categories.index')->with('success', "Kategori {$category->name} berhasil ditambahkan.");
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return view('categories.edit', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        Activity::log('CATEGORY_UPDATED', "Updated category {$category->name}", $category);

        return redirect()->route('categories.index')->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->books()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh buku.');
        }

        Activity::log('CATEGORY_DELETED', "Deleted category {$category->name}");

        $category->delete();

        return redirect()->route('categories.index')->with('success', "Kategori {$category->name} berhasil dihapus.");
    }
}
