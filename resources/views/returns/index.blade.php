@extends('layouts.app')

@section('title', 'Pengembalian')
@section('page', 'returns')

@php
  $conditionOptions = collect($conditions)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
  $itemOptions = $items->mapWithKeys(fn ($i) => [
    $i->id => $i->loan->code.' — '.$i->book->title.' ('.$i->loan->member->name.')'.($i->due_at ? ' · jatuh tempo '.$i->due_at->format('d/m/Y') : ''),
  ])->all();
@endphp

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-8/12 lg:flex-none">
      <form method="POST" action="{{ route('returns.store') }}" enctype="multipart/form-data">
        @csrf
        <x-card title="Proses Pengembalian" subtitle="Pilih eksemplar yang dikembalikan, unggah bukti foto buku, lalu tentukan kondisinya.">
          <x-slot:actions>
            <x-button type="submit" icon="fas fa-check">Proses Pengembalian</x-button>
          </x-slot:actions>

          @if ($items->isEmpty())
            <p class="py-6 text-sm text-center text-slate-400">Tidak ada eksemplar yang sedang dipinjam.</p>
          @else
            <x-form.select
              name="loan_item_id"
              label="Eksemplar yang dikembalikan"
              :options="$itemOptions"
              :selected="old('loan_item_id')"
              required
              searchable
              placeholder="Cari kode peminjaman, judul buku, atau nama anggota..."
            />
            <x-form.file
              name="photo"
              label="Bukti foto buku yang dikembalikan"
              accept="image/png,image/jpeg,image/webp"
              buttonText="Pilih foto"
              required
              help="JPG/PNG/WEBP, maks 4 MB. Wajib sebagai bukti kondisi buku saat diterima."
            />
            <x-form.select name="condition" label="Kondisi buku saat kembali" :options="$conditionOptions" :selected="old('condition', 'GOOD')" required />
            <x-form.textarea name="notes" label="Catatan (opsional)" rows="2" maxlength="255" counter />
          @endif
        </x-card>
      </form>
    </div>
  </div>
@endsection
