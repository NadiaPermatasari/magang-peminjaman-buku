@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('page', 'users')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-9/12 lg:flex-none">
      <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <x-card title="Tambah Pengguna" subtitle="Buat akun baru. Pengguna akan menerima notifikasi selamat datang.">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('users.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan pengguna</x-button>
          </x-slot:actions>
          @include('users._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
