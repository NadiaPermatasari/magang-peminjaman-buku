@extends('layouts.app')

@section('title', 'Kategori')
@section('page', 'categories')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Kategori" :subtitle="$categories->total().' kategori'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('categories.index') }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori..." class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
      </form>
      @can('create', App\Models\Category::class)
        <x-button href="{{ route('categories.create') }}" icon="fas fa-plus">Tambah kategori</x-button>
      @endcan
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Nama</th>
            <th class="{{ $th }}">Deskripsi</th>
            <th class="{{ $th }} text-center">Jumlah Buku</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($categories as $category)
            <tr>
              <td class="{{ $td }}"><h6 class="mb-0 text-sm dark:text-white">{{ $category->name }}</h6></td>
              <td class="{{ $td }} text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($category->description, 60) ?: '—' }}</td>
              <td class="{{ $td }} text-center text-sm">{{ $category->books_count }}</td>
              <td class="{{ $td }} text-center">
                @can('update', $category)
                  <a href="{{ route('categories.edit', $category) }}" class="mr-3 text-xs font-semibold text-slate-400 hover:text-blue-500"><i class="mr-1 fas fa-pen"></i>Ubah</a>
                @endcan
                @can('delete', $category)
                  <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-0 text-xs font-semibold bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600"><i class="mr-1 fas fa-trash"></i>Hapus</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-6 text-sm text-center dark:text-white/80">Belum ada kategori.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($categories->hasPages())
      <div class="px-6 pt-4">{{ $categories->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
