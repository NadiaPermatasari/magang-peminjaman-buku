@extends('layouts.auth')

@section('title', 'Konfirmasi Kata Sandi')
@section('page', 'confirm-password')

@section('content')
  <x-auth-card heading="Konfirmasi kata sandi" subheading="Ini area sensitif aplikasi. Mohon konfirmasi kata sandi Anda sebelum melanjutkan.">
    <form method="POST" action="{{ route('password.confirm.store') }}">
      @csrf
      <x-form.input name="password" label="Kata sandi" type="password" autocomplete="current-password" required autofocus icon="fas fa-lock" password-toggle />
      <x-button type="submit" size="lg" block icon="fas fa-lock">Konfirmasi</x-button>
    </form>

    <x-slot:footer>
      <p class="mx-auto mb-0 leading-normal text-sm"><a href="{{ route('dashboard') }}" class="font-semibold text-slate-700">Kembali ke dashboard</a></p>
    </x-slot:footer>
  </x-auth-card>
@endsection
