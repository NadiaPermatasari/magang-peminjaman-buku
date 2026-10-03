@extends('layouts.app')

@section('title', 'Buku')
@section('page', 'books')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Buku" :subtitle="$books->total().' judul'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('books.index') }}" class="flex flex-wrap items-center gap-2">
        <select name="category" onchange="this.form.submit()" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20">
          <option value="">Semua kategori</option>
          @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
          @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul/ISBN..." class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
      </form>
      @can('create', App\Models\Book::class)
        <x-button href="{{ route('books.create') }}" icon="fas fa-plus">Tambah buku</x-button>
      @endcan
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Buku</th>
            <th class="{{ $th }}">Kategori</th>
            <th class="{{ $th }} text-center">Eksemplar</th>
            <th class="{{ $th }} text-center">Status</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($books as $book)
            <tr>
              <td class="{{ $td }}">
                <div class="flex items-center px-2 py-1">
                  <img src="{{ $book->cover_url ?? asset('assets/img/theme/bootstrap.jpg') }}" class="object-cover w-9 h-12 mr-3 rounded-md" alt="" />
                  <div>
                    <h6 class="mb-0 text-sm leading-normal dark:text-white">{{ $book->title }}</h6>
                    <p class="mb-0 text-xs leading-tight text-slate-400">{{ $book->isbn ?: '—' }}</p>
                  </div>
                </div>
              </td>
              <td class="{{ $td }} text-xs text-slate-400">{{ $book->category->name }}</td>
              <td class="{{ $td }} text-center text-sm">{{ $book->copies_count }}</td>
              <td class="{{ $td }} text-center">
                @if ($book->is_active)
                  <span class="bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Aktif</span>
                @else
                  <span class="bg-gradient-to-tl from-slate-600 to-slate-300 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Nonaktif</span>
                @endif
              </td>
              <td class="{{ $td }} text-center">
                @can('update', $book)
                  <a href="{{ route('books.edit', $book) }}" class="mr-3 text-xs font-semibold text-slate-400 hover:text-blue-500"><i class="mr-1 fas fa-pen"></i>Ubah</a>
                @endcan
                @can('delete', $book)
                  <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline" onsubmit="return confirm('Hapus buku {{ addslashes($book->title) }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-0 text-xs font-semibold bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600"><i class="mr-1 fas fa-trash"></i>Hapus</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-6 text-sm text-center dark:text-white/80">Belum ada buku.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($books->hasPages())
      <div class="px-6 pt-4">{{ $books->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
