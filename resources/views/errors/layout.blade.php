@extends('layouts.base')

@section('title', $code ?? 'Error')
@section('page', 'error')
@section('body-class', 'm-0 font-sans antialiased font-normal bg-white text-base leading-default text-slate-500')

@section('body')
  <main class="mt-0 transition-all duration-200 ease-in-out">
    <section>
      <div class="relative flex items-center min-h-screen p-0 overflow-hidden bg-center bg-cover">
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-tl from-blue-500 to-violet-500 opacity-90"></div>
        <div class="container z-10">
          <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 mx-auto text-center lg:flex-0 shrink-0 lg:w-6/12">
              <h1 class="mt-0 mb-2 font-bold text-white text-9xl leading-none">@yield('code')</h1>
              <h4 class="mb-2 font-bold text-white uppercase">@yield('heading')</h4>
              <p class="mb-6 text-white opacity-80">@yield('message')</p>
              <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-block px-8 py-3 mb-0 font-bold leading-normal text-center text-blue-500 align-middle transition-all bg-white border-0 rounded-lg shadow-md cursor-pointer text-sm ease-in tracking-tight-rem hover:shadow-xs hover:-translate-y-px active:opacity-85">
                <i class="mr-1 fas fa-arrow-left"></i> Go back
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection
