@extends('layouts.app')

@section('title', 'Tambah Anggota')
@section('page', 'members')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-10/12 lg:flex-none">
      <form method="POST" action="{{ route('members.store') }}">
        @csrf
        <x-card title="Tambah Anggota">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('members.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan</x-button>
          </x-slot:actions>
          @include('members._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
