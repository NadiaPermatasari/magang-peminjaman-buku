@extends('layouts.app')

@section('title', $loan->code)
@section('page', 'loans')

@php
  $pendingExtension = $loan->extensions->firstWhere('status', App\Enums\ExtensionStatus::PENDING);
  $canRequestExtension = auth()->user()->can('create', [App\Models\LoanExtension::class, $loan]) && ! $pendingExtension;
@endphp

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
      <x-card :title="$loan->code" :subtitle="'Diajukan oleh '.$loan->member->name">
        <x-slot:actions>
          <span class="bg-gradient-to-tl {{ $loan->status->badgeColor() }} px-3 text-xs rounded-1.8 py-1.5 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $loan->status->label() }}</span>

          @can('approve', $loan)
            <form method="POST" action="{{ route('loans.approve', $loan) }}" onsubmit="return confirm('Setujui peminjaman ini? Tindakan ini akan mereservasi eksemplar buku.');" class="inline">
              @csrf
              <x-button type="submit" variant="success" size="sm" icon="fas fa-check">Setujui</x-button>
            </form>
          @endcan

          @can('cancel', $loan)
            <form method="POST" action="{{ route('loans.cancel', $loan) }}" onsubmit="return confirm('Batalkan pengajuan ini?');" class="inline">
              @csrf
              <x-button type="submit" variant="outline" size="sm" icon="fas fa-times">Batalkan</x-button>
            </form>
          @endcan
        </x-slot:actions>

        @can('reject', $loan)
          <form method="POST" action="{{ route('loans.reject', $loan) }}" class="p-3 mb-4 rounded-lg bg-gray-50 dark:bg-slate-900" onsubmit="return confirm('Tolak pengajuan ini?');">
            @csrf
            <div class="flex flex-wrap items-end gap-2">
              <div class="flex-1 min-w-48">
                <x-form.input name="reason" label="Alasan penolakan (wajib)" required class="mb-0" />
              </div>
              <x-button type="submit" variant="danger" size="sm" icon="fas fa-ban">Tolak Pengajuan</x-button>
            </div>
          </form>
        @endcan

        <div class="flex flex-wrap -mx-3 mb-4 text-sm">
          <div class="w-1/2 px-3 mb-2 md:w-1/4"><span class="text-slate-400">Diajukan</span><br><span class="dark:text-white">{{ $loan->requested_at->format('d M Y H:i') }}</span></div>
          @if ($loan->approved_at)
            <div class="w-1/2 px-3 mb-2 md:w-1/4"><span class="text-slate-400">Disetujui</span><br><span class="dark:text-white">{{ $loan->approved_at->format('d M Y H:i') }} — {{ $loan->approvedBy?->name }}</span></div>
          @endif
          @if ($loan->pickup_deadline)
            <div class="w-1/2 px-3 mb-2 md:w-1/4"><span class="text-slate-400">Batas Pengambilan</span><br><span class="dark:text-white">{{ $loan->pickup_deadline->format('d M Y H:i') }}</span></div>
          @endif
          @if ($loan->due_at)
            <div class="w-1/2 px-3 mb-2 md:w-1/4"><span class="text-slate-400">Jatuh Tempo</span><br><span class="dark:text-white">{{ $loan->due_at->format('d M Y H:i') }}</span></div>
          @endif
          @if ($loan->rejection_reason)
            <div class="w-full px-3 mb-2"><span class="text-slate-400">Alasan Penolakan</span><br><span class="text-red-600">{{ $loan->rejection_reason }}</span></div>
          @endif
        </div>

        <div class="overflow-x-auto">
          <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="px-4 py-2 text-xs font-bold text-left uppercase text-slate-400">Buku</th>
                <th class="px-4 py-2 text-xs font-bold text-center uppercase text-slate-400">Eksemplar</th>
                <th class="px-4 py-2 text-xs font-bold text-center uppercase text-slate-400">Status</th>
                <th class="px-4 py-2 text-xs font-bold text-center uppercase text-slate-400">Bukti Foto</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($loan->items as $item)
                <tr class="border-b dark:border-white/10">
                  <td class="px-4 py-2 text-sm dark:text-white">{{ $item->book->title }}</td>
                  <td class="px-4 py-2 text-xs text-center text-slate-400">{{ $item->bookCopy?->barcode ?? '—' }}</td>
                  <td class="px-4 py-2 text-xs text-center">
                    <span class="bg-gradient-to-tl {{ $item->status->badgeColor() }} px-2 py-1 rounded-lg text-white font-bold uppercase text-xxs">{{ $item->status->label() }}</span>
                  </td>
                  <td class="px-4 py-2 text-xs text-center">
                    <div class="flex items-center justify-center gap-3">
                      @if ($item->handover_photo_url)
                        <a href="{{ $item->handover_photo_url }}" target="_blank" rel="noopener" class="font-semibold text-blue-500 hover:underline"><i class="mr-1 fas fa-camera"></i>Serah terima</a>
                      @endif
                      @if ($item->return_photo_url)
                        <a href="{{ $item->return_photo_url }}" target="_blank" rel="noopener" class="font-semibold text-emerald-600 hover:underline"><i class="mr-1 fas fa-camera"></i>Pengembalian</a>
                      @endif
                      @unless ($item->handover_photo_url || $item->return_photo_url)
                        <span class="text-slate-400">—</span>
                      @endunless
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </x-card>
    </div>

    {{-- Perpanjangan / banding peminjaman --}}
    <div class="w-full max-w-full px-3 mt-6">
      <x-card title="Perpanjangan / Banding" subtitle="Riwayat permintaan perpanjangan untuk peminjaman ini.">
        @if ($canRequestExtension)
          <form method="POST" action="{{ route('loan-extensions.store', $loan) }}" class="p-3 mb-4 rounded-lg bg-gray-50 dark:bg-slate-900">
            @csrf
            <div class="flex flex-wrap items-end -mx-3">
              <div class="w-full max-w-full px-3 md:w-3/12">
                <x-form.input name="days" label="Tambahan hari" type="number" min="1" :max="setting('loan_duration_days', 7)" :value="old('days', 3)" required class="mb-0" />
              </div>
              <div class="flex-1 min-w-56 px-3">
                <x-form.input name="reason" label="Alasan perpanjangan (wajib)" :value="old('reason')" required class="mb-0" />
              </div>
              <div class="px-3">
                <x-button type="submit" size="sm" icon="fas fa-calendar-plus">Ajukan Perpanjangan</x-button>
              </div>
            </div>
          </form>
        @elseif ($pendingExtension && $loan->member->user_id === auth()->id())
          <p class="p-3 mb-4 text-xs rounded-lg bg-orange-500/10 text-orange-600">
            <i class="mr-1 fas fa-hourglass-half"></i>Pengajuan perpanjangan {{ $pendingExtension->days }} hari sedang menunggu keputusan petugas.
          </p>
        @endif

        @forelse ($loan->extensions as $extension)
          <div class="p-3 mb-3 border border-solid rounded-lg border-gray-200 dark:border-white/10 last:mb-0">
            <div class="flex flex-wrap items-start justify-between gap-2">
              <div class="text-sm">
                <p class="mb-1 dark:text-white">
                  <strong>+{{ $extension->days }} hari</strong>
                  <span class="text-slate-400">diajukan {{ $extension->requested_at->format('d M Y H:i') }}</span>
                  @if ($extension->requestedBy)
                    <span class="text-slate-400">oleh {{ $extension->requestedBy->name }}</span>
                  @endif
                </p>
                <p class="mb-1 text-xs text-slate-400">Alasan: {{ $extension->reason }}</p>
                @if ($extension->decided_at)
                  <p class="mb-0 text-xs text-slate-400">
                    Diputuskan {{ $extension->decided_at->format('d M Y H:i') }}{{ $extension->decidedBy ? ' oleh '.$extension->decidedBy->name : '' }}.
                    @if ($extension->decision_note)
                      Catatan: {{ $extension->decision_note }}
                    @endif
                    @if ($extension->new_due_at)
                      Jatuh tempo baru: {{ $extension->new_due_at->format('d M Y H:i') }}.
                    @endif
                  </p>
                @endif
              </div>
              <span class="bg-gradient-to-tl {{ $extension->status->badgeColor() }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $extension->status->label() }}</span>
            </div>

            @can('decide', $extension)
              <div class="flex flex-wrap items-end gap-2 pt-3 mt-3 border-t border-solid border-gray-100 dark:border-white/5">
                <form method="POST" action="{{ route('loan-extensions.approve', $extension) }}" onsubmit="return confirm('Setujui perpanjangan ini? Jatuh tempo peminjaman akan digeser.');" class="flex items-end flex-1 gap-2 min-w-56">
                  @csrf
                  <div class="flex-1">
                    <x-form.input name="note" label="Catatan (opsional)" class="mb-0" />
                  </div>
                  <x-button type="submit" variant="success" size="sm" icon="fas fa-check">Setujui</x-button>
                </form>
                <form method="POST" action="{{ route('loan-extensions.reject', $extension) }}" onsubmit="return confirm('Tolak perpanjangan ini?');" class="flex items-end flex-1 gap-2 min-w-56">
                  @csrf
                  <div class="flex-1">
                    <x-form.input name="note" label="Alasan penolakan (wajib)" required class="mb-0" />
                  </div>
                  <x-button type="submit" variant="danger" size="sm" icon="fas fa-ban">Tolak</x-button>
                </form>
              </div>
            @endcan
          </div>
        @empty
          <p class="mb-0 text-sm text-slate-400">Belum ada permintaan perpanjangan untuk peminjaman ini.</p>
        @endforelse
      </x-card>
    </div>
  </div>
@endsection
