{{--
  Top navbar (breadcrumb, sidenav toggle, notifications, profile menu).
  The page title comes from @section('title'); $breadcrumb can be passed by the
  view to override the "Pages" parent label.
--}}
@php
  $pageTitle = trim($__env->yieldContent('title', 'Dashboard'));
@endphp

<!-- Navbar -->
<nav class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all ease-in shadow-none duration-250 rounded-2xl lg:flex-nowrap lg:justify-start" navbar-main navbar-scroll="false">
  <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
    <div class="flex items-center">
      {{-- sidenav toggle: slides the menu on mobile, collapses / expands it on desktop --}}
      <a href="javascript:;" class="block p-0 text-sm text-white transition-all ease-nav-brand mr-4" sidenav-trigger aria-label="Toggle sidenav" title="Toggle sidenav">
        <div class="w-4.5 overflow-hidden">
          <i class="ease mb-0.75 relative block h-0.5 rounded-sm bg-white transition-all"></i>
          <i class="ease mb-0.75 relative block h-0.5 rounded-sm bg-white transition-all"></i>
          <i class="ease relative block h-0.5 rounded-sm bg-white transition-all"></i>
        </div>
      </a>
      <nav>
      <!-- breadcrumb -->
      <ol class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16">
        <li class="text-sm leading-normal">
          <a class="text-white opacity-50" href="{{ route('dashboard') }}">{{ $breadcrumb ?? 'Pages' }}</a>
        </li>
        <li class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']" aria-current="page">{{ $pageTitle }}</li>
      </ol>
      <h6 class="mb-0 font-bold text-white capitalize">{{ $pageTitle }}</h6>
      </nav>
    </div>

    <div class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto lg:justify-end">
      <ul class="flex flex-row items-center justify-end pl-0 mb-0 list-none md-max:w-full">
        @include('layouts.partials.notifications-dropdown')

        @include('layouts.partials.profile-dropdown')
      </ul>
    </div>
  </div>
</nav>
<!-- end Navbar -->
