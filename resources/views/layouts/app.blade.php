{{--
  Dashboard layout: sidenav + navbar + content + footer.

  Sections a page can define:
    @section('title')       page title (also used in the breadcrumb)
    @section('page')        page key for <body data-page>, used by Argon's JS
    @section('background')  override the top background strip (see profile page)
    @section('navbar')      override the whole navbar (see profile page)
    @section('content')     page content
--}}
@extends('layouts.base')

{{-- Authenticated app pages are behind a login wall and add nothing for
     search engines — never index them. --}}
@section('robots', 'noindex, nofollow')

@section('body')
  @hasSection('background')
    @yield('background')
  @else
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>
  @endif

  @include('layouts.partials.sidenav')

  <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
    @hasSection('navbar')
      @yield('navbar')
    @else
      @include('layouts.partials.navbar')
    @endif

    <div class="w-full px-6 py-6 mx-auto">
      @include('layouts.partials.alerts')

      @yield('content')

      @include('layouts.partials.footer')
    </div>
  </main>
@endsection
