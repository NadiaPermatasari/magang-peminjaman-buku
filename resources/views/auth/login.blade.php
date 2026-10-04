@extends('layouts.auth')

@section('title', 'Masuk')
@section('page', 'sign-in')
@section('hide-auth-navbar', true)

@section('content')
  <x-auth-card :heading="'Masuk ke '.app_name()" subheading="Gunakan akun yang diberikan oleh admin perpustakaan.">
    <form role="form" method="POST" action="{{ route('login.store') }}">
      @csrf
      <x-form.input name="email" label="Email" type="email" autocomplete="email" autofocus required icon="fas fa-envelope" />
      <x-form.input name="password" label="Kata sandi" type="password" autocomplete="current-password" required icon="fas fa-lock" password-toggle />

      <x-form.checkbox name="remember" label="Ingat saya" :checked="old('remember')" />

      <x-button type="submit" size="lg" block icon="fas fa-sign-in-alt">Masuk</x-button>
    </form>

    <x-slot:footer>
      <p class="mx-auto mb-0 leading-normal text-sm">
        <a href="{{ route('password.request') }}" class="font-semibold text-slate-700">Lupa kata sandi?</a>
      </p>
    </x-slot:footer>
  </x-auth-card>
@endsection
