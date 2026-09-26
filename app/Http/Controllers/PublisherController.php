<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublisherRequest;
use App\Models\Publisher;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Publisher::class);

        $search = trim((string) $request->query('search', ''));

        $publishers = Publisher::query()
            ->withCount('books')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('publishers.index', ['publishers' => $publishers]);
    }

    public function create()
    {
        $this->authorize('create', Publisher::class);

        return view('publishers.create', ['publisher' => new Publisher]);
    }

    public function store(PublisherRequest $request): RedirectResponse
    {
        $publisher = Publisher::create($request->validated());

        Activity::log('PUBLISHER_CREATED', "Created publisher {$publisher->name}", $publisher);

        return redirect()->route('publishers.index')->with('success', "Penerbit {$publisher->name} berhasil ditambahkan.");
    }

    public function edit(Publisher $publisher)
    {
        $this->authorize('update', $publisher);

        return view('publishers.edit', ['publisher' => $publisher]);
    }

    public function update(PublisherRequest $request, Publisher $publisher): RedirectResponse
    {
        $publisher->update($request->validated());

        Activity::log('PUBLISHER_UPDATED', "Updated publisher {$publisher->name}", $publisher);

        return redirect()->route('publishers.index')->with('success', "Penerbit {$publisher->name} berhasil diperbarui.");
    }

    public function destroy(Publisher $publisher): RedirectResponse
    {
        $this->authorize('delete', $publisher);

        if ($publisher->books()->exists()) {
            return back()->with('error', 'Penerbit tidak dapat dihapus karena masih digunakan oleh buku.');
        }

        Activity::log('PUBLISHER_DELETED', "Deleted publisher {$publisher->name}");

        $publisher->delete();

        return redirect()->route('publishers.index')->with('success', "Penerbit {$publisher->name} berhasil dihapus.");
    }
}
