@extends('layouts.app')

@section('title', 'Ubah Anggota')
@section('page', 'members')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-10/12 lg:flex-none">
      <form method="POST" action="{{ route('members.update', $member) }}">
        @csrf
        @method('PUT')
        <x-card :title="$member->name" :subtitle="$member->member_number">
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('members.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan perubahan</x-button>
          </x-slot:actions>
          @include('members._form')
        </x-card>
      </form>
    </div>
  </div>
@endsection
