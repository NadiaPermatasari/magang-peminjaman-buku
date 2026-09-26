@extends('layouts.app')

@section('title', 'Laporan Denda')
@section('page', 'reports')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Laporan Denda" :subtitle="$fines->total().' catatan denda'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('reports.fines') }}" class="flex flex-wrap items-center gap-2">
        <select name="status" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20">
          <option value="">Semua status</option>
          @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
          @endforeach
        </select>
        <x-button type="submit" variant="dark" size="sm" icon="fas fa-filter">Filter</x-button>
        @can('reports.export')
          <x-button href="{{ route('reports.fines', array_merge(request()->query(), ['export' => 'csv'])) }}" variant="outline" size="sm" icon="fas fa-download">Export CSV</x-button>
        @endcan
      </form>
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Anggota</th>
            <th class="{{ $th }}">Buku</th>
            <th class="{{ $th }} text-center">Jumlah</th>
            <th class="{{ $th }} text-center">Status</th>
            <th class="{{ $th }} text-center">Dihitung</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($fines as $fine)
            <tr>
              <td class="{{ $td }} text-sm dark:text-white">{{ $fine->member->name }}</td>
              <td class="{{ $td }} text-xs text-slate-400">{{ $fine->loanItem->book->title }}</td>
              <td class="{{ $td }} text-center text-sm font-semibold dark:text-white">Rp{{ number_format($fine->amount, 0, ',', '.') }}</td>
              <td class="{{ $td }} text-center">
                <span class="bg-gradient-to-tl {{ $fine->status->badgeColor() }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $fine->status->label() }}</span>
              </td>
              <td class="{{ $td }} text-center text-xs text-slate-400">{{ $fine->calculated_at->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-6 text-sm text-center dark:text-white/80">Tidak ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($fines->hasPages())
      <div class="px-6 pt-4">{{ $fines->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
