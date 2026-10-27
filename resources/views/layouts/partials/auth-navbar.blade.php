<div class="container sticky top-0 z-sticky">
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 flex-0">
      <!-- Navbar -->
      <nav class="absolute top-0 left-0 right-0 z-30 flex flex-wrap items-center px-4 py-2 m-4 mb-0 shadow-sm sm:m-6 rounded-xl bg-white/80 backdrop-blur-2xl backdrop-saturate-200 lg:flex-nowrap lg:justify-start">
        <div class="flex items-center justify-between w-full min-w-0 gap-2 p-0 mx-auto sm:px-6">
          <a class="flex-1 min-w-0 py-2 text-sm font-bold break-words text-slate-700" href="{{ route('login') }}">{{ app_name() }}</a>
          <div class="flex items-center shrink-0">
            <ul class="flex flex-col pl-0 mx-auto mb-0 list-none lg:flex-row xl:ml-auto">
              @auth
                <li>
                  <a class="flex items-center px-4 py-2 mr-2 font-normal transition-all ease-in-out duration-250 text-sm text-slate-700 lg:px-2" href="{{ route('dashboard') }}">
                    <i class="mr-1 fa fa-chart-pie opacity-60"></i>
                    Dashboard
                  </a>
                </li>
                <li>
                  <a class="block px-4 py-2 mr-2 font-normal transition-all ease-in-out duration-250 text-sm text-slate-700 lg:px-2" href="{{ route('profile') }}">
                    <i class="mr-1 fa fa-user opacity-60"></i>
                    Profil
                  </a>
                </li>
              @else
                <li>
                  <a class="block px-4 py-2 mr-2 font-normal transition-all ease-in-out duration-250 text-sm text-slate-700 lg:px-2" href="{{ route('login') }}">
                    <i class="mr-1 fas fa-key opacity-60"></i>
                    Masuk
                  </a>
                </li>
              @endauth
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </div>
</div>
