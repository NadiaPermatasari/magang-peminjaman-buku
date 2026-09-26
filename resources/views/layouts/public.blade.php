{{--
  Public marketing layout for the landing page (guests) — simple top bar
  (logo + Masuk) and footer, no sidenav/app chrome. Content is editable by
  Super Admin via Pengaturan > Halaman Depan.
--}}
@extends('layouts.base')

@section('body-class', 'm-0 font-sans antialiased font-normal bg-white text-start text-base leading-default text-slate-500')

@section('body')
  <nav class="sticky top-0 z-sticky bg-white/90 backdrop-blur-md border-b border-solid border-gray-100 dark:bg-slate-900/90 dark:border-white/10">
    <div class="flex items-center justify-between max-w-6xl px-6 py-3 mx-auto">
      <a href="{{ route('home') }}" class="flex items-center text-slate-700 dark:text-white">
        @if (setting('logo'))
          <img src="{{ asset('storage/'.setting('logo')) }}" class="h-8 mr-2" alt="{{ app_name() }}" />
        @else
          <i class="mr-2 text-2xl text-blue-500 ni ni-books"></i>
        @endif
        <span class="font-semibold">{{ app_name() }}</span>
      </a>
      <div class="flex items-center gap-2">
        @auth
          <x-button href="{{ route('dashboard') }}" size="sm" icon="fas fa-th-large">Ke Dashboard</x-button>
        @else
          <x-button href="{{ route('login') }}" size="sm" icon="fas fa-sign-in-alt">Masuk</x-button>
        @endauth
      </div>
    </div>
  </nav>

  <main>
    @yield('content')
  </main>

  <footer class="py-10 mt-10 border-t border-solid border-gray-100 dark:border-white/10">
    <div class="max-w-6xl px-6 mx-auto text-sm text-center text-slate-400">
      <p class="mb-2 font-semibold text-slate-600 dark:text-white">{{ setting('institution_name') ?: app_name() }}</p>
      <p class="mb-2">
        {{ collect([setting('address'), setting('phone'), setting('email')])->filter()->join(' · ') }}
      </p>
      <p class="mb-0">&copy; {{ date('Y') }} {{ app_name() }}. {{ setting('footer_text') }}</p>
    </div>
  </footer>
@endsection
