@extends('layouts.app')

@section('title', 'Tambah Eksemplar')
@section('page', 'book-copies')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-10/12 lg:flex-none">
      <form method="POST" action="{{ route('book-copies.store') }}">
        @csrf
        <x-card title="Tambah Eksemplar">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('book-copies.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan</x-button>
          </x-slot:actions>
          @include('book-copies._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
