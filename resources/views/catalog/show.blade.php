@extends('layouts.app')

@section('title', $book->title)
@section('page', 'catalog')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 md:w-4/12 lg:w-3/12">
      <img src="{{ $book->cover_url ?? asset('assets/img/theme/bootstrap.jpg') }}" class="w-full mb-4 shadow-xl rounded-2xl" alt="{{ $book->title }}" />
    </div>
    <div class="w-full max-w-full px-3 md:w-8/12 lg:w-9/12">
      <x-card>
        <x-slot:actions>
          @if (Route::has('loans.create') && auth()->user()->can('loans.create') && $availableCopies > 0)
            <x-button href="{{ route('loans.create', ['book' => $book->uuid]) }}" icon="fas fa-cart-plus">Ajukan Peminjaman</x-button>
          @endif
        </x-slot:actions>

        <h4 class="mb-1 dark:text-white">{{ $book->title }}</h4>
        <p class="mb-4 text-sm text-slate-400">{{ $book->authors->pluck('name')->join(', ') ?: 'Tanpa penulis' }}</p>

        <span class="px-2 py-1 text-xs font-bold uppercase rounded-md {{ $availableCopies > 0 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600' }}">
          {{ $availableCopies > 0 ? $availableCopies.' eksemplar tersedia' : 'Tidak ada eksemplar tersedia' }}
        </span>

        <hr class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent" />

        <div class="flex flex-wrap -mx-3 mb-4 text-sm">
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">Kategori</span><br><span class="dark:text-white">{{ $book->category->name }}</span></div>
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">Penerbit</span><br><span class="dark:text-white">{{ $book->publisher->name }}</span></div>
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">ISBN</span><br><span class="dark:text-white">{{ $book->isbn ?: '—' }}</span></div>
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">Tahun Terbit</span><br><span class="dark:text-white">{{ $book->publication_year ?: '—' }}</span></div>
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">Edisi</span><br><span class="dark:text-white">{{ $book->edition ?: '—' }}</span></div>
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">Bahasa</span><br><span class="dark:text-white">{{ $book->language ?: '—' }}</span></div>
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">Jumlah Halaman</span><br><span class="dark:text-white">{{ $book->page_count ?: '—' }}</span></div>
          <div class="w-1/2 px-3 mb-2 md:w-1/3"><span class="text-slate-400">Rak</span><br><span class="dark:text-white">{{ $book->rack?->code ?: '—' }}</span></div>
        </div>

        @if ($book->description)
          <p class="text-sm leading-normal dark:text-white/80">{{ $book->description }}</p>
        @endif
      </x-card>
    </div>
  </div>
@endsection
