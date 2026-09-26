@extends('layouts.app')

@section('title', $loan->code)
@section('page', 'loans')

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
                <th class="px-4 py-2 text-xs font-bold text-center uppercase text-slate-400">Denda</th>
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
                  <td class="px-4 py-2 text-xs text-center text-slate-400">
                    @if ($item->fine)
                      Rp{{ number_format($item->fine->amount, 0, ',', '.') }} ({{ $item->fine->status->label() }})
                    @else
                      —
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </x-card>
    </div>
  </div>
@endsection
