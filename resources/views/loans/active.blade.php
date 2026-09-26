@extends('layouts.app')

@section('title', 'Peminjaman Aktif')
@section('page', 'loans')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Peminjaman Aktif" :subtitle="$loans->total().' transaksi'" :padding="false">
    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Kode</th>
            <th class="{{ $th }}">Anggota</th>
            <th class="{{ $th }}">Buku</th>
            <th class="{{ $th }} text-center">Jatuh Tempo</th>
            <th class="{{ $th }} text-center">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($loans as $loan)
            <tr>
              <td class="{{ $td }} text-sm font-semibold dark:text-white"><a href="{{ route('loans.show', $loan) }}" class="hover:text-blue-500">{{ $loan->code }}</a></td>
              <td class="{{ $td }} text-sm dark:text-white">{{ $loan->member->name }}</td>
              <td class="{{ $td }} text-xs text-slate-400">{{ $loan->items->pluck('book.title')->join(', ') }}</td>
              <td class="{{ $td }} text-center text-xs text-slate-400">{{ $loan->due_at?->format('d/m/Y') }}</td>
              <td class="{{ $td }} text-center">
                <span class="bg-gradient-to-tl {{ $loan->status->badgeColor() }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $loan->status->label() }}</span>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-6 text-sm text-center dark:text-white/80">Tidak ada peminjaman aktif.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($loans->hasPages())
      <div class="px-6 pt-4">{{ $loans->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
