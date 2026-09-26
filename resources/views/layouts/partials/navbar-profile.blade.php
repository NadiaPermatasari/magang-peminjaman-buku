{{-- Navbar variant used on the profile page (transparent, overlaps the header image). --}}
@php $pageTitle = trim($__env->yieldContent('title', 'Profile')); @endphp

<nav class="absolute z-20 flex flex-wrap items-center justify-between w-full px-6 py-2 -mt-56 text-white transition-all ease-in shadow-none duration-250 lg:flex-nowrap lg:justify-start" navbar-profile navbar-scroll="true">
  <div class="flex items-center justify-between w-full px-6 py-1 mx-auto flex-wrap-inherit">
    <div class="flex items-center">
      {{-- sidenav toggle: slides the menu on mobile, collapses / expands it on desktop --}}
      <a href="javascript:;" class="block p-0 text-white transition-all ease-in-out text-sm mr-4" sidenav-trigger aria-label="Toggle sidenav" title="Toggle sidenav">
        <div class="w-4.5 overflow-hidden">
          <i class="ease mb-0.75 relative block h-0.5 rounded-sm bg-white transition-all"></i>
          <i class="ease mb-0.75 relative block h-0.5 rounded-sm bg-white transition-all"></i>
          <i class="ease relative block h-0.5 rounded-sm bg-white transition-all"></i>
        </div>
      </a>
      <nav>
      <!-- breadcrumb -->
      <ol class="flex flex-wrap pt-1 pl-2 pr-4 mr-12 bg-transparent rounded-lg sm:mr-16">
        <li class="leading-normal text-sm">
          <a class="opacity-50" href="{{ route('dashboard') }}">Pages</a>
        </li>
        <li class="text-sm pl-2 capitalize leading-normal before:float-left before:pr-2 before:content-['/']" aria-current="page">{{ $pageTitle }}</li>
      </ol>
      <h6 class="mb-2 ml-2 font-bold text-white capitalize dark:text-white">{{ $pageTitle }}</h6>
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
