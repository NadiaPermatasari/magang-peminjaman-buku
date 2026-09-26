@extends('layouts.app')

@section('title', 'Rak')
@section('page', 'racks')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Rak" :subtitle="$racks->total().' rak'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('racks.index') }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari rak..." class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
      </form>
      @can('create', App\Models\Rack::class)
        <x-button href="{{ route('racks.create') }}" icon="fas fa-plus">Tambah rak</x-button>
      @endcan
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Kode</th>
            <th class="{{ $th }}">Nama</th>
            <th class="{{ $th }}">Lokasi</th>
            <th class="{{ $th }} text-center">Jumlah Buku</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($racks as $rack)
            <tr>
              <td class="{{ $td }} text-sm font-semibold dark:text-white">{{ $rack->code }}</td>
              <td class="{{ $td }} text-sm dark:text-white">{{ $rack->name }}</td>
              <td class="{{ $td }} text-xs text-slate-400">{{ $rack->location ?: '—' }}</td>
              <td class="{{ $td }} text-center text-sm">{{ $rack->books_count }}</td>
              <td class="{{ $td }} text-center">
                @can('update', $rack)
                  <a href="{{ route('racks.edit', $rack) }}" class="mr-3 text-xs font-semibold text-slate-400 hover:text-blue-500"><i class="mr-1 fas fa-pen"></i>Ubah</a>
                @endcan
                @can('delete', $rack)
                  <form method="POST" action="{{ route('racks.destroy', $rack) }}" class="inline" onsubmit="return confirm('Hapus rak {{ addslashes($rack->code) }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-0 text-xs font-semibold bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600"><i class="mr-1 fas fa-trash"></i>Hapus</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-6 text-sm text-center dark:text-white/80">Belum ada rak.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($racks->hasPages())
      <div class="px-6 pt-4">{{ $racks->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
