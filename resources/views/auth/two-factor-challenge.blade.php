@extends('layouts.auth')

@section('title', 'Verifikasi Dua Langkah')
@section('page', 'two-factor-challenge')

@section('content')
  <x-auth-card heading="Verifikasi dua langkah" subheading="Masukkan kode 6 digit dari aplikasi authenticator Anda, atau salah satu kode pemulihan.">
    <form method="POST" action="{{ route('two-factor.login.store') }}">
      @csrf
      <x-form.input name="code" label="Kode autentikasi" placeholder="123456" inputmode="numeric" autocomplete="one-time-code" autofocus icon="fas fa-mobile-alt" />

      <div class="relative my-4 text-center">
        <hr class="h-px mx-0 my-0 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent" />
        <span class="absolute px-3 text-xs -translate-x-1/2 -translate-y-1/2 bg-white left-1/2 top-1/2 text-slate-400">atau</span>
      </div>

      <x-form.input name="recovery_code" label="Kode pemulihan" placeholder="xxxxxxxx-xxxxxxxx" autocomplete="one-time-code" icon="fas fa-life-ring" />

      <x-button type="submit" size="lg" block icon="fas fa-sign-in-alt">Masuk</x-button>
    </form>

    <x-slot:footer>
      <p class="mx-auto mb-0 leading-normal text-sm">Kehilangan perangkat? Gunakan kode pemulihan di atas, atau <a href="{{ route('login') }}" class="font-semibold text-slate-700">kembali ke halaman masuk</a>.</p>
    </x-slot:footer>
  </x-auth-card>
@endsection
