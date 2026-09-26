@extends('layouts.app')

@section('title', 'Laporan Pengembalian')
@section('page', 'reports')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Laporan Pengembalian" :subtitle="$rows->total().' pengembalian'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('reports.returns') }}" class="flex flex-wrap items-center gap-2">
        <input type="date" name="from" value="{{ request('from') }}" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
        <input type="date" name="to" value="{{ request('to') }}" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
        <x-button type="submit" variant="dark" size="sm" icon="fas fa-filter">Filter</x-button>
        @can('reports.export')
          <x-button href="{{ route('reports.returns', array_merge(request()->query(), ['export' => 'csv'])) }}" variant="outline" size="sm" icon="fas fa-download">Export CSV</x-button>
        @endcan
      </form>
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Kode</th>
            <th class="{{ $th }}">Anggota</th>
            <th class="{{ $th }}">Buku</th>
            <th class="{{ $th }} text-center">Kondisi</th>
            <th class="{{ $th }} text-center">Dikembalikan</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td class="{{ $td }} text-sm font-semibold dark:text-white">{{ $row->code }}</td>
              <td class="{{ $td }} text-sm dark:text-white">{{ $row->member_name }}</td>
              <td class="{{ $td }} text-xs text-slate-400">{{ $row->title }}</td>
              <td class="{{ $td }} text-center text-xs text-slate-400">{{ $row->condition_on_return }}</td>
              <td class="{{ $td }} text-center text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($row->returned_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-6 text-sm text-center dark:text-white/80">Tidak ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($rows->hasPages())
      <div class="px-6 pt-4">{{ $rows->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
