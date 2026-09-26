@extends('layouts.app')

@section('title', 'Tambah Role')
@section('page', 'roles')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
      <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        <x-card title="Tambah Role">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('roles.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan</x-button>
          </x-slot:actions>
          @include('roles._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
