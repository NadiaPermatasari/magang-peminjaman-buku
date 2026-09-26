@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')
@section('page', 'loans')

@php
  $bookOptions = $books->mapWithKeys(fn ($b) => [$b->id => $b->title.' ('.$b->available_copies_count.' tersedia)'])->all();
  $preselectedBook = $preselected ? $books->firstWhere('uuid', $preselected) : null;
@endphp

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-8/12 lg:flex-none">
      <form method="POST" action="{{ route('loans.store') }}">
        @csrf
        <x-card title="Ajukan Peminjaman" :subtitle="'Maksimum '.setting('max_active_loans', 3).' buku aktif sekaligus.'">
          <x-slot:actions>
            <x-button type="submit" icon="fas fa-paper-plane">Kirim Pengajuan</x-button>
          </x-slot:actions>

          <x-form.select name="book_ids" label="Pilih buku" :options="$bookOptions" :selected="$preselectedBook ? [$preselectedBook->id] : []" multiple searchable required placeholder="Cari judul buku..." />
          <x-form.textarea name="notes" label="Catatan (opsional)" rows="2" maxlength="255" counter />
        </x-card>
      </form>
    </div>
  </div>
@endsection
