{{--
  Auth layout used by sign in / sign up / forgot & reset password pages.
--}}
@extends('layouts.base')

@section('robots', 'noindex, nofollow')

@section('body-class', 'm-0 font-sans antialiased font-normal bg-white text-start text-base leading-default text-slate-500')

@section('body')
  @include('layouts.partials.auth-navbar')

  <main class="mt-0 transition-all duration-200 ease-in-out">
    @yield('content')
  </main>

  @include('layouts.partials.auth-footer')
@endsection
