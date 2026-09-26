@extends('layouts.app')

@section('title', 'Katalog')
@section('page', 'catalog')

@section('content')
  <x-card title="Katalog Buku" :subtitle="$books->total().' judul tersedia'">
    <x-slot:actions>
      <form method="GET" action="{{ route('catalog.index') }}" class="flex flex-wrap items-center gap-2">
        <select name="category" onchange="this.form.submit()" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20">
          <option value="">Semua kategori</option>
          @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
          @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul/ISBN..." class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
      </form>
    </x-slot:actions>

    <div class="flex flex-wrap -mx-3">
      @forelse ($books as $book)
        <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 lg:w-1/3 xl:w-1/4">
          <a href="{{ route('catalog.show', $book) }}" class="relative flex flex-col h-full min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border hover:-translate-y-1 transition-all">
            <img src="{{ $book->cover_url ?? asset('assets/img/theme/bootstrap.jpg') }}" class="object-cover w-full h-48 rounded-t-2xl" alt="{{ $book->title }}" />
            <div class="flex-auto p-4">
              <h6 class="mb-1 text-sm dark:text-white">{{ $book->title }}</h6>
              <p class="mb-2 text-xs text-slate-400">{{ $book->authors->pluck('name')->join(', ') ?: 'Tanpa penulis' }}</p>
              <span class="px-2 py-0.5 text-xxs font-bold uppercase rounded-md {{ $book->available_count > 0 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600' }}">
                {{ $book->available_count > 0 ? $book->available_count.' tersedia' : 'Tidak tersedia' }}
              </span>
            </div>
          </a>
        </div>
      @empty
        <div class="w-full px-3 py-12 text-center text-sm text-slate-400">Tidak ada buku ditemukan.</div>
      @endforelse
    </div>

    @if ($books->hasPages())
      <div class="pt-4">{{ $books->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
