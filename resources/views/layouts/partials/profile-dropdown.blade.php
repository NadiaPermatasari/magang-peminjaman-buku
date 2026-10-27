{{-- Navbar profile dropdown: avatar + name trigger, Profil/Keluar menu. --}}
<li class="relative flex items-center pl-1 sm:pl-3">
  <button type="button" class="flex items-center justify-center min-w-11 h-11 px-1 text-sm text-white bg-transparent border-0 rounded-lg cursor-pointer" dropdown-trigger aria-controls="profile-menu" aria-expanded="false" aria-label="Menu profil">
    <img src="{{ auth()->user()->avatar_url }}" class="object-cover w-8 h-8 rounded-full shrink-0" alt="" />
    <span class="hidden max-w-40 ml-2 font-semibold truncate lg:inline">{{ auth()->user()->name }}</span>
    <i class="ml-1 text-xs fas fa-chevron-down" aria-hidden="true"></i>
  </button>

  <ul id="profile-menu" dropdown-menu class="hidden absolute right-0 top-full z-50 w-56 max-w-[calc(100vw-2rem)] py-2 mt-2 overflow-y-auto text-sm text-left list-none bg-white shadow-xl rounded-lg dark:shadow-dark-xl dark:bg-slate-850 text-slate-500">
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
