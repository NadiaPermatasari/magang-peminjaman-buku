@extends('layouts.app')

@section('title', 'Ubah Buku')
@section('page', 'books')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
      <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <x-card :title="$book->title">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('books.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan perubahan</x-button>
          </x-slot:actions>
          @include('books._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
