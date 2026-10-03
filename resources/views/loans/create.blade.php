@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')
@section('page', 'loans')

@php
  $bookOptions = $books->mapWithKeys(fn ($b) => [$b->id => $b->title.' ('.$b->available_copies_count.' tersedia)'])->all();
  $preselectedBook = $preselected ? $books->firstWhere('uuid', $preselected) : null;
  $memberOptions = $members->mapWithKeys(fn ($m) => [$m->id => $m->name.' — '.$m->member_number])->all();
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

          @if ($onBehalf)
            <p class="p-3 mb-4 text-xs rounded-lg bg-blue-500/10 text-blue-600">
              <i class="mr-1 fas fa-info-circle"></i>Akun Anda bukan akun anggota, jadi pengajuan ini dibuat atas nama anggota yang Anda pilih.
            </p>
            <x-form.select name="member_id" label="Anggota" :options="$memberOptions" :selected="old('member_id')" required searchable placeholder="Cari nama atau nomor anggota..." />
          @endif

          <x-form.select name="book_ids" label="Pilih buku" :options="$bookOptions" :selected="$preselectedBook ? [$preselectedBook->id] : []" multiple searchable required placeholder="Cari judul buku..." />
          <x-form.textarea name="notes" label="Catatan (opsional)" rows="2" maxlength="255" counter />
        </x-card>
      </form>
    </div>
  </div>
@endsection
