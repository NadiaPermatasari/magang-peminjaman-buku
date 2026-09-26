@extends('layouts.app')

@section('title', 'Tambah Penulis')
@section('page', 'authors')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-8/12 lg:flex-none">
      <form method="POST" action="{{ route('authors.store') }}">
        @csrf
        <x-card title="Tambah Penulis">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('authors.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan</x-button>
          </x-slot:actions>
          @include('authors._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
