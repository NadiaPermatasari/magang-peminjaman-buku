@extends('layouts.app')

@section('title', 'Tambah Buku')
@section('page', 'books')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
      <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data">
        @csrf
        <x-card title="Tambah Buku">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('books.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan</x-button>
          </x-slot:actions>
          @include('books._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
