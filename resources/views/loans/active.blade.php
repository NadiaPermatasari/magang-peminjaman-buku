@extends('layouts.app')

@section('title', 'Peminjaman Aktif')
@section('page', 'loans')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-3 align-middle bg-transparent border-b dark:border-white/40 shadow-transparent';
@endphp

@section('content')
  <x-card title="Peminjaman Aktif" :subtitle="$loans->total().' transaksi'" :padding="false">
    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Kode</th>
            <th class="{{ $th }}">Anggota</th>
            <th class="{{ $th }}">Buku &amp; Bukti Foto</th>
            <th class="{{ $th }} text-center">Jatuh Tempo</th>
            <th class="{{ $th }} text-center">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($loans as $loan)
            <tr>
              <td class="{{ $td }} text-sm font-semibold whitespace-nowrap dark:text-white"><a href="{{ route('loans.show', $loan) }}" class="hover:text-blue-500">{{ $loan->code }}</a></td>
              <td class="{{ $td }} text-sm whitespace-nowrap dark:text-white">
                {{ $loan->member->name }}
                <span class="block text-xs text-slate-400">{{ $loan->member->member_number }}</span>
              </td>
              <td class="{{ $td }} text-xs">
                @foreach ($loan->items as $item)
                  <div class="flex flex-wrap items-center gap-2 py-1 {{ $loop->last ? '' : 'border-b border-solid border-gray-100 dark:border-white/5' }}">
                    <span class="dark:text-white">{{ $item->book->title }}</span>
                    <span class="text-slate-400">{{ $item->bookCopy?->barcode }}</span>

                    @if ($item->handover_photo_url)
                      <a href="{{ $item->handover_photo_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:underline">
                        <img src="{{ $item->handover_photo_url }}" alt="Bukti serah terima" class="object-cover w-8 h-8 rounded" />
                        Lihat bukti
                      </a>
                    @else
                      <span class="text-slate-400"><i class="mr-1 fas fa-image"></i>Belum ada bukti foto</span>
                    @endif

                    @can('uploadHandoverPhoto', $loan)
                      <form method="POST" action="{{ route('loans.items.photo', [$loan, $item]) }}" enctype="multipart/form-data" class="flex items-center gap-1">
                        @csrf
                        <input
                          type="file"
                          name="photo"
                          accept="image/png,image/jpeg,image/webp"
                          required
                          onchange="this.form.requestSubmit()"
                          class="max-w-40 text-xxs text-slate-400 file:mr-1 file:rounded file:border-0 file:bg-slate-700 file:px-2 file:py-1 file:text-xxs file:font-bold file:text-white file:cursor-pointer"
                        />
                        <noscript><button type="submit" class="px-2 py-1 text-xxs font-bold text-white rounded bg-slate-700">Unggah</button></noscript>
                      </form>
                    @endcan
                  </div>
                @endforeach
                <x-form.error name="photo" />
              </td>
              <td class="{{ $td }} text-center text-xs whitespace-nowrap text-slate-400">{{ $loan->due_at?->format('d/m/Y') }}</td>
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
