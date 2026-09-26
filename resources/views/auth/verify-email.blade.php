@extends('layouts.auth')

@section('title', 'Verifikasi Email')
@section('page', 'verify-email')

@section('content')
  <x-auth-card heading="Verifikasi email Anda" subheading="Silakan konfirmasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan.">
    <p class="mb-4 text-sm">Tidak menerima email? Kami akan kirimkan lagi.</p>

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <x-button type="submit" size="lg" block icon="fas fa-paper-plane">Kirim ulang email verifikasi</x-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
      @csrf
      <button type="submit" class="p-0 text-sm font-semibold bg-transparent border-0 cursor-pointer text-slate-700 hover:underline">Keluar</button>
    </form>

    <x-slot:footer>
      <p class="mx-auto mb-0 leading-normal text-sm">Sudah terverifikasi? <a href="{{ route('dashboard') }}" class="font-semibold text-transparent bg-clip-text bg-gradient-to-tl from-blue-500 to-violet-500">Ke dashboard</a></p>
    </x-slot:footer>
  </x-auth-card>
@endsection
