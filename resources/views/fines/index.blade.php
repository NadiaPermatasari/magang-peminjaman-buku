@extends('layouts.app')

@section('title', 'Denda')
@section('page', 'fines')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Denda" :subtitle="$fines->total().' catatan denda'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('fines.index') }}">
        <select name="status" onchange="this.form.submit()" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20">
          <option value="">Semua status</option>
          @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
          @endforeach
        </select>
      </form>
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Anggota</th>
            <th class="{{ $th }}">Buku</th>
            <th class="{{ $th }} text-center">Terlambat</th>
            <th class="{{ $th }} text-center">Jumlah</th>
            <th class="{{ $th }} text-center">Status</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($fines as $fine)
            <tr>
              <td class="{{ $td }} text-sm dark:text-white">{{ $fine->member->name }}</td>
              <td class="{{ $td }} text-xs text-slate-400">{{ $fine->loanItem->book->title }}</td>
              <td class="{{ $td }} text-center text-xs text-slate-400">{{ $fine->late_days }} hari</td>
              <td class="{{ $td }} text-center text-sm font-semibold dark:text-white">Rp{{ number_format($fine->amount, 0, ',', '.') }}</td>
              <td class="{{ $td }} text-center">
                <span class="bg-gradient-to-tl {{ $fine->status->badgeColor() }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $fine->status->label() }}</span>
              </td>
              <td class="{{ $td }} text-center">
                @can('markPaid', $fine)
                  @if ($fine->status->value === 'UNPAID')
                    <form method="POST" action="{{ route('fines.mark-paid', $fine) }}" class="inline" onsubmit="return confirm('Tandai denda ini lunas?');">
                      @csrf
                      <button type="submit" class="mr-2 text-xs font-semibold text-emerald-600 bg-transparent border-0 cursor-pointer"><i class="mr-1 fas fa-check"></i>Lunas</button>
                    </form>
                  @endif
                @endcan
                @can('waive', $fine)
                  @if ($fine->status->value === 'UNPAID')
                    <button type="button" onclick="document.getElementById('waive-{{ $fine->id }}').classList.toggle('hidden')" class="text-xs font-semibold text-red-600 bg-transparent border-0 cursor-pointer"><i class="mr-1 fas fa-hand-holding-heart"></i>Bebaskan</button>
                  @endif
                @endcan
              </td>
            </tr>
            @can('waive', $fine)
              @if ($fine->status->value === 'UNPAID')
                <tr id="waive-{{ $fine->id }}" class="hidden">
                  <td colspan="6" class="p-3 bg-gray-50 dark:bg-slate-900">
                    <form method="POST" action="{{ route('fines.waive', $fine) }}" class="flex flex-wrap items-end gap-2">
                      @csrf
                      <div class="flex-1 min-w-48">
                        <x-form.input name="reason" label="Alasan pembebasan denda (wajib)" required class="mb-0" />
                      </div>
                      <x-button type="submit" variant="danger" size="sm">Bebaskan Denda</x-button>
                    </form>
                  </td>
                </tr>
              @endif
            @endcan
          @empty
            <tr><td colspan="6" class="p-6 text-sm text-center dark:text-white/80">Tidak ada catatan denda.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($fines->hasPages())
      <div class="px-6 pt-4">{{ $fines->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
