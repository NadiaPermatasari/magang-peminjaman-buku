@extends('layouts.app')

@section('title', 'Perpanjangan Peminjaman')
@section('page', 'loan-extensions')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-3 align-middle bg-transparent border-b dark:border-white/40 shadow-transparent';
@endphp

@section('content')
  <x-card
    title="Perpanjangan / Banding Peminjaman"
    :subtitle="$seesAll ? $extensions->total().' pengajuan — setujui untuk menggeser jatuh tempo.' : 'Pengajuan perpanjangan peminjaman Anda.'"
    :padding="false"
  >
    <x-slot:actions>
      <form method="GET" action="{{ route('loan-extensions.index') }}" class="flex flex-wrap items-center gap-2">
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
            <th class="{{ $th }}">Peminjaman</th>
            @if ($seesAll)
              <th class="{{ $th }}">Anggota</th>
            @endif
            <th class="{{ $th }}">Permintaan</th>
            <th class="{{ $th }} text-center">Jatuh Tempo</th>
            <th class="{{ $th }} text-center">Status</th>
            <th class="{{ $th }}">Keputusan</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($extensions as $extension)
            <tr>
              <td class="{{ $td }} text-sm font-semibold whitespace-nowrap dark:text-white">
                <a href="{{ route('loans.show', $extension->loan) }}" class="hover:text-blue-500">{{ $extension->loan->code }}</a>
                <span class="block text-xs font-normal text-slate-400">{{ $extension->loan->items->pluck('book.title')->join(', ') }}</span>
              </td>
              @if ($seesAll)
                <td class="{{ $td }} text-sm whitespace-nowrap dark:text-white">
                  {{ $extension->loan->member->name }}
                  <span class="block text-xs text-slate-400">{{ $extension->loan->member->member_number }}</span>
                </td>
              @endif
              <td class="{{ $td }} text-xs">
                <span class="text-sm font-semibold dark:text-white">+{{ $extension->days }} hari</span>
                <span class="block text-slate-400">{{ $extension->reason }}</span>
                <span class="block text-slate-400">Diajukan {{ $extension->requested_at->format('d/m/Y H:i') }}</span>
              </td>
              <td class="{{ $td }} text-center text-xs whitespace-nowrap text-slate-400">
                {{ $extension->previous_due_at?->format('d/m/Y') ?? '—' }}
                @if ($extension->new_due_at)
                  <i class="mx-1 fas fa-arrow-right"></i>
                  <span class="font-semibold text-emerald-600">{{ $extension->new_due_at->format('d/m/Y') }}</span>
                @endif
              </td>
              <td class="{{ $td }} text-center">
                <span class="bg-gradient-to-tl {{ $extension->status->badgeColor() }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $extension->status->label() }}</span>
              </td>
              <td class="{{ $td }} text-xs">
                @can('decide', $extension)
                  <div class="flex flex-col gap-2 min-w-56">
                    <form method="POST" action="{{ route('loan-extensions.approve', $extension) }}" onsubmit="return confirm('Setujui perpanjangan ini? Jatuh tempo peminjaman akan digeser.');" class="flex items-end gap-2">
                      @csrf
                      <div class="flex-1">
                        <x-form.input name="note" placeholder="Catatan (opsional)" class="mb-0" />
                      </div>
                      <x-button type="submit" variant="success" size="sm" icon="fas fa-check">Setujui</x-button>
                    </form>
                    <form method="POST" action="{{ route('loan-extensions.reject', $extension) }}" onsubmit="return confirm('Tolak perpanjangan ini?');" class="flex items-end gap-2">
                      @csrf
                      <div class="flex-1">
                        <x-form.input name="note" placeholder="Alasan penolakan (wajib)" required class="mb-0" />
                      </div>
                      <x-button type="submit" variant="danger" size="sm" icon="fas fa-ban">Tolak</x-button>
                    </form>
                  </div>
                @else
                  @if ($extension->decided_at)
                    <span class="text-slate-400">
                      {{ $extension->decided_at->format('d/m/Y H:i') }}{{ $extension->decidedBy ? ' — '.$extension->decidedBy->name : '' }}
                      @if ($extension->decision_note)
                        <span class="block">{{ $extension->decision_note }}</span>
                      @endif
                    </span>
                  @else
                    <span class="text-slate-400">Menunggu petugas</span>
                  @endif
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="{{ $seesAll ? 6 : 5 }}" class="p-6 text-sm text-center dark:text-white/80">Belum ada pengajuan perpanjangan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($extensions->hasPages())
      <div class="px-6 pt-4">{{ $extensions->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
