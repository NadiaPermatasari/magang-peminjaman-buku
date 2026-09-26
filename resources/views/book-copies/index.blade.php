@extends('layouts.app')

@section('title', 'Eksemplar Buku')
@section('page', 'book-copies')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Eksemplar Buku" :subtitle="$copies->total().' eksemplar'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('book-copies.index') }}" class="flex flex-wrap items-center gap-2">
        <select name="status" onchange="this.form.submit()" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20">
          <option value="">Semua status</option>
          @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
          @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barcode/judul..." class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
      </form>
      @can('create', App\Models\BookCopy::class)
        <x-button href="{{ route('book-copies.create') }}" icon="fas fa-plus">Tambah eksemplar</x-button>
      @endcan
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Barcode</th>
            <th class="{{ $th }}">Buku</th>
            <th class="{{ $th }} text-center">Kondisi</th>
            <th class="{{ $th }} text-center">Status</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($copies as $copy)
            <tr>
              <td class="{{ $td }} text-sm font-semibold dark:text-white">{{ $copy->barcode }}</td>
              <td class="{{ $td }} text-sm dark:text-white">{{ $copy->book->title }}</td>
              <td class="{{ $td }} text-center text-xs text-slate-400">{{ $copy->condition->label() }}</td>
              <td class="{{ $td }} text-center">
                <span class="bg-gradient-to-tl {{ $copy->status->badgeColor() }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $copy->status->label() }}</span>
              </td>
              <td class="{{ $td }} text-center">
                @can('update', $copy)
                  <a href="{{ route('book-copies.edit', $copy) }}" class="mr-3 text-xs font-semibold text-slate-400 hover:text-blue-500"><i class="mr-1 fas fa-pen"></i>Ubah</a>
                @endcan
                @can('delete', $copy)
                  <form method="POST" action="{{ route('book-copies.destroy', $copy) }}" class="inline" onsubmit="return confirm('Hapus eksemplar {{ addslashes($copy->barcode) }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-0 text-xs font-semibold bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600"><i class="mr-1 fas fa-trash"></i>Hapus</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-6 text-sm text-center dark:text-white/80">Belum ada eksemplar.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($copies->hasPages())
      <div class="px-6 pt-4">{{ $copies->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
