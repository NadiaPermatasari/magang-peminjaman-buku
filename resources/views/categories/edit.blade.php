@extends('layouts.app')

@section('title', 'Ubah Kategori')
@section('page', 'categories')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-8/12 lg:flex-none">
      <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf
        @method('PUT')
        <x-card :title="$category->name">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('categories.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan perubahan</x-button>
          </x-slot:actions>
          @include('categories._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
