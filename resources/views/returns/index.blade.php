@extends('layouts.app')

@section('title', 'Pengembalian')
@section('page', 'returns')

@php
  $conditionOptions = collect($conditions)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
@endphp

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-8/12 lg:flex-none">
      <form method="POST" action="{{ route('returns.store') }}">
        @csrf
        <x-card title="Proses Pengembalian" subtitle="Scan barcode eksemplar yang dikembalikan, lalu pilih kondisi buku.">
          <x-slot:actions>
            <x-button type="submit" icon="fas fa-check">Proses Pengembalian</x-button>
          </x-slot:actions>

          <x-form.input name="barcode" label="Barcode eksemplar" placeholder="Scan atau ketik barcode..." icon="fas fa-barcode" autofocus required />
          <x-form.select name="condition" label="Kondisi buku saat kembali" :options="$conditionOptions" selected="GOOD" required />
          <x-form.textarea name="notes" label="Catatan (opsional)" rows="2" maxlength="255" counter />
        </x-card>
      </form>
    </div>
  </div>
@endsection
