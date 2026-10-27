{{-- Navbar variant used on the profile page (transparent, overlaps the header image). --}}
@php $pageTitle = trim($__env->yieldContent('title', 'Profile')); @endphp

<nav class="app-navbar absolute z-30 w-full px-4 py-2 -mt-56 text-white shadow-none sm:px-6" navbar-profile navbar-scroll="true">
  <div class="flex items-start justify-between w-full min-w-0 gap-2 px-0 py-1 mx-auto sm:px-6">
    <div class="flex items-start flex-1 min-w-0 gap-2 sm:gap-4">
      {{-- sidenav toggle: slides the menu on mobile, collapses / expands it on desktop --}}
      <button type="button" class="inline-flex items-center justify-center p-0 text-sm text-white bg-transparent border-0 rounded-lg cursor-pointer w-11 h-11 shrink-0" sidenav-trigger aria-controls="app-sidenav" aria-expanded="false" aria-label="Buka menu navigasi" title="Menu navigasi">
        <div class="w-4.5 overflow-hidden">
          <i class="ease mb-0.75 relative block h-0.5 rounded-sm bg-white transition-all"></i>
          <i class="ease mb-0.75 relative block h-0.5 rounded-sm bg-white transition-all"></i>
          <i class="ease relative block h-0.5 rounded-sm bg-white transition-all"></i>
        </div>
      </button>
      <nav class="min-w-0" aria-label="Lokasi halaman">
      <!-- breadcrumb -->
      <ol class="hidden flex-wrap pt-1 bg-transparent rounded-lg sm:flex">
        <li class="leading-normal text-sm">
          <a class="opacity-50" href="{{ route('dashboard') }}">Beranda</a>
        </li>
        <li class="text-sm pl-2 capitalize leading-normal before:float-left before:pr-2 before:content-['/']" aria-current="page">{{ $pageTitle }}</li>
      </ol>
      <h6 class="mt-2 mb-0 text-sm font-bold text-white break-words sm:mt-0 sm:text-base">{{ $pageTitle }}</h6>
      </nav>
    </div>

    <div class="flex items-center shrink-0">
      <ul class="flex items-center justify-end pl-0 mb-0 list-none">
        @include('layouts.partials.notifications-dropdown')

        @include('layouts.partials.profile-dropdown')
      </ul>
    </div>
  </div>
</nav>
