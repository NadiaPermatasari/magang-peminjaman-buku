@extends('layouts.auth')

@section('title', 'Reset Kata Sandi')
@section('page', 'reset-password')

@section('content')
  <x-auth-card heading="Reset kata sandi" subheading="Pilih kata sandi baru untuk akun Anda.">
    <form role="form" method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $request->route('token') }}" />
      <x-form.input name="email" label="Email" type="email" :value="$request->email" autocomplete="email" required icon="fas fa-envelope" />
      <x-form.input name="password" label="Kata sandi baru" type="password" autocomplete="new-password" required icon="fas fa-lock" password-toggle />
      <x-form.input name="password_confirmation" label="Konfirmasi kata sandi baru" type="password" autocomplete="new-password" required icon="fas fa-lock" password-toggle />
      <x-button type="submit" size="lg" block icon="fas fa-check">Reset kata sandi</x-button>
    </form>

    <x-slot:footer>
      <p class="mx-auto mb-0 leading-normal text-sm">Kembali ke <a href="{{ route('login') }}" class="font-semibold text-slate-700">halaman masuk</a></p>
    </x-slot:footer>
  </x-auth-card>
@endsection
