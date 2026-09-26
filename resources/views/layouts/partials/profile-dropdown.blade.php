{{-- Navbar profile dropdown: avatar + name trigger, Profil/Keluar menu. --}}
<li class="relative flex items-center pl-4">
  <p class="hidden transform-dropdown-show"></p>
  <a href="javascript:;" class="flex items-center text-sm text-white transition-all ease-nav-brand" dropdown-trigger aria-expanded="false" aria-label="Menu profil">
    <img src="{{ auth()->user()->avatar_url }}" class="inline-flex items-center justify-center w-8 h-8 mr-2 text-white rounded-full object-cover" alt="{{ auth()->user()->name }}" />
    <span class="hidden font-semibold sm:inline">{{ auth()->user()->name }}</span>
    <i class="ml-1 text-xs fas fa-chevron-down"></i>
  </a>

  <ul dropdown-menu class="text-sm transform-dropdown before:font-awesome before:leading-default before:duration-350 before:ease lg:shadow-3xl duration-250 min-w-44 w-56 before:sm:right-8 before:text-5.5 pointer-events-none absolute right-0 top-0 z-50 origin-top list-none rounded-lg border-0 border-solid border-transparent dark:shadow-dark-xl dark:bg-slate-850 bg-white bg-clip-padding py-2 text-left text-slate-500 opacity-0 transition-all before:absolute before:right-2 before:left-auto before:top-0 before:z-50 before:inline-block before:font-normal before:text-white before:antialiased before:transition-all before:content-['\f0d8'] sm:-mr-6 lg:absolute lg:right-0 lg:left-auto lg:mt-2 lg:block lg:cursor-pointer">
    <li>
      <a href="{{ route('profile') }}" class="dark:hover:bg-slate-900 ease flex items-center py-2 clear-both w-full whitespace-nowrap bg-transparent px-4 text-left duration-300 hover:bg-gray-200 hover:text-slate-700 lg:transition-colors text-slate-700 dark:text-white">
        <i class="mr-2 fas fa-user"></i> Profil
      </a>
    </li>
    <li>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="dark:hover:bg-slate-900 ease flex items-center py-2 w-full whitespace-nowrap bg-transparent border-0 px-4 text-left duration-300 hover:bg-gray-200 hover:text-slate-700 lg:transition-colors cursor-pointer text-slate-700 dark:text-white">
          <i class="mr-2 fas fa-sign-out-alt"></i> Keluar
        </button>
      </form>
    </li>
  </ul>
</li>
