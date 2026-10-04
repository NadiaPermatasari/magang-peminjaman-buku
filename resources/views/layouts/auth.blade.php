{{--
  Auth layout used by sign in / sign up / forgot & reset password pages.
--}}
@extends('layouts.base')

@section('robots', 'noindex, nofollow')

@section('body-class', 'm-0 font-sans antialiased font-normal bg-white text-start text-base leading-default text-slate-500')

@section('body')
  {{-- Halaman yang mendefinisikan section `hide-auth-navbar` tampil tanpa bar
       atas — mis. halaman masuk, di mana tautan "Masuk" hanya menunjuk ke
       halaman itu sendiri. --}}
  @unless (View::hasSection('hide-auth-navbar'))
    @include('layouts.partials.auth-navbar')
  @endunless

  <main class="mt-0 transition-all duration-200 ease-in-out">
    @yield('content')
  </main>

  @include('layouts.partials.auth-footer')
@endsection
