@extends('layouts.app')

@section('title', 'Siap Diambil')
@section('page', 'loans')

@section('content')
  <x-card title="Siap Diambil" subtitle="Pilih eksemplar yang diserahkan dan unggah bukti fotonya." :padding="false">
    <div class="p-6">
      @forelse ($loans as $loan)
        @php
          $pendingItems = $loan->items->where('status', App\Enums\LoanStatus::APPROVED)->filter(fn ($i) => $i->bookCopy);
          $copyOptions = $pendingItems->mapWithKeys(fn ($i) => [$i->bookCopy->barcode => $i->book->title.' — '.$i->bookCopy->barcode])->all();
        @endphp
        <div class="p-4 mb-4 border border-solid rounded-xl border-gray-200 dark:border-white/10 last:mb-0">
          <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
            <div>
              <a href="{{ route('loans.show', $loan) }}" class="text-sm font-semibold dark:text-white hover:text-blue-500">{{ $loan->code }}</a>
              <p class="mb-0 text-xs text-slate-400">{{ $loan->member->name }} ({{ $loan->member->member_number }}) — batas ambil {{ $loan->pickup_deadline?->format('d M Y H:i') }}</p>
            </div>
          </div>

          <ul class="mb-3 text-sm">
            @foreach ($loan->items as $item)
              <li class="flex items-center justify-between py-1 border-b border-solid last:border-0 border-gray-100 dark:border-white/5">
                <span class="dark:text-white">{{ $item->book->title }} <span class="text-xs text-slate-400">({{ $item->bookCopy?->barcode }})</span></span>
                <span class="bg-gradient-to-tl {{ $item->status->badgeColor() }} px-2 py-0.5 rounded-md text-white font-bold uppercase text-xxs">{{ $item->status->label() }}</span>
              </li>
            @endforeach
          </ul>

          @if ($copyOptions)
            <form method="POST" action="{{ route('loans.handover', $loan) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3 p-3 rounded-lg bg-gray-50 dark:bg-slate-900">
              @csrf
              <div class="flex-1 min-w-56">
                <x-form.select name="barcode" label="Eksemplar yang diserahkan" :options="$copyOptions" required class="mb-0" placeholder="Pilih eksemplar..." />
              </div>
              <div class="flex-1 min-w-56">
                <x-form.file name="photo" label="Bukti foto serah terima" accept="image/png,image/jpeg,image/webp" buttonText="Pilih foto" help="JPG/PNG/WEBP, maks 4 MB. Opsional." class="mb-0" />
              </div>
              <x-button type="submit" variant="info" size="sm" icon="fas fa-handshake">Serahkan</x-button>
            </form>
          @else
            <p class="mb-0 text-xs text-slate-400">Tidak ada eksemplar yang menunggu diserahkan pada pengajuan ini.</p>
          @endif
        </div>
      @empty
        <p class="py-6 text-sm text-center text-slate-400">Tidak ada peminjaman yang siap diambil.</p>
      @endforelse
    </div>

    @if ($loans->hasPages())
      <div class="px-6 pb-4">{{ $loans->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
