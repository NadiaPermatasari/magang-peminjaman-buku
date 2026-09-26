@extends('layouts.app')

@section('title', 'Ubah Rak')
@section('page', 'racks')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-8/12 lg:flex-none">
      <form method="POST" action="{{ route('racks.update', $rack) }}">
        @csrf
        @method('PUT')
        <x-card :title="$rack->code.' — '.$rack->name">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('racks.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan perubahan</x-button>
          </x-slot:actions>
          @include('racks._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
