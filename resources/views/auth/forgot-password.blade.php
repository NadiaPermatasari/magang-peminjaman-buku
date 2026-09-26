@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')
@section('page', 'forgot-password')

@section('content')
  <x-auth-card heading="Lupa kata sandi?" subheading="Masukkan email Anda, kami akan mengirimkan tautan untuk mengatur ulang kata sandi.">
    <form role="form" method="POST" action="{{ route('password.email') }}">
      @csrf
      <x-form.input name="email" label="Email" type="email" autocomplete="email" autofocus required icon="fas fa-envelope" />
      <x-button type="submit" size="lg" block icon="fas fa-paper-plane">Kirim tautan reset</x-button>
    </form>

    <x-slot:footer>
      <p class="mx-auto mb-0 leading-normal text-sm">Sudah ingat? <a href="{{ route('login') }}" class="font-semibold text-slate-700">Kembali masuk</a></p>
    </x-slot:footer>
  </x-auth-card>
@endsection
