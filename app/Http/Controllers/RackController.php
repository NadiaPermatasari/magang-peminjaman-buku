<?php

namespace App\Http\Controllers;

use App\Http\Requests\RackRequest;
use App\Models\Rack;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Rack::class);

        $search = trim((string) $request->query('search', ''));

        $racks = Rack::query()
            ->withCount('books')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->orderBy('code')
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('racks.index', ['racks' => $racks]);
    }

    public function create()
    {
        $this->authorize('create', Rack::class);

        return view('racks.create', ['rack' => new Rack]);
    }

    public function store(RackRequest $request): RedirectResponse
    {
        $rack = Rack::create($request->validated());

        Activity::log('RACK_CREATED', "Created rack {$rack->code}", $rack);

        return redirect()->route('racks.index')->with('success', "Rak {$rack->code} berhasil ditambahkan.");
    }

    public function edit(Rack $rack)
    {
        $this->authorize('update', $rack);

        return view('racks.edit', ['rack' => $rack]);
    }

    public function update(RackRequest $request, Rack $rack): RedirectResponse
    {
        $rack->update($request->validated());

        Activity::log('RACK_UPDATED', "Updated rack {$rack->code}", $rack);

        return redirect()->route('racks.index')->with('success', "Rak {$rack->code} berhasil diperbarui.");
    }

    public function destroy(Rack $rack): RedirectResponse
    {
        $this->authorize('delete', $rack);

        if ($rack->books()->exists()) {
            return back()->with('error', 'Rak tidak dapat dihapus karena masih digunakan oleh buku.');
        }

        Activity::log('RACK_DELETED', "Deleted rack {$rack->code}");

        $rack->delete();

        return redirect()->route('racks.index')->with('success', "Rak {$rack->code} berhasil dihapus.");
    }
}
