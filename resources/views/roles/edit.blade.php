@extends('layouts.app')

@section('title', 'Ubah Role')
@section('page', 'roles')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
      <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')
        <x-card :title="str_replace('-', ' ', $role->name)">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('roles.index') }}">Batal</x-button>
            @unless ($isProtected ?? false)
              <x-button type="submit" icon="fas fa-save">Simpan perubahan</x-button>
            @endunless
          </x-slot:actions>
          @include('roles._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
