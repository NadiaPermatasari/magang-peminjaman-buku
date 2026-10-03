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

      {{-- Form terpisah (bukan nested) untuk akun login anggota. --}}
      <form method="POST" action="{{ route('members.account', $member) }}" class="mt-6">
        @csrf
        <x-card
          :title="$member->user ? 'Atur Ulang Kata Sandi Akun' : 'Buat Akun Login'"
          :subtitle="$member->user
            ? 'Kata sandi baru untuk '.$member->user->email.' — serahkan langsung ke anggota.'
            : 'Buat akun agar anggota ini bisa masuk dan mengajukan peminjaman sendiri.'"
        >
          <x-slot:actions>
            <x-button type="submit" variant="info" icon="fas fa-key">
              {{ $member->user ? 'Simpan kata sandi' : 'Buat akun' }}
            </x-button>
          </x-slot:actions>

          @unless ($member->email)
            <p class="p-3 mb-4 text-xs rounded-lg bg-orange-500/10 text-orange-600">
              <i class="mr-1 fas fa-exclamation-triangle"></i>Anggota ini belum punya email. Isi dan simpan emailnya dulu di form di atas.
            </p>
          @endunless

          <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="password" label="Kata sandi" type="password" autocomplete="new-password" required password-toggle help="Minimal 8 karakter." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" required />
            </div>
          </div>
        </x-card>
      </form>
    </div>
  </div>
@endsection
