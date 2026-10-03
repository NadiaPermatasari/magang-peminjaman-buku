@php
  // Sidenav menu (spec §41), redesigned as collapsible groups (native
  // <details>/<summary>, no extra JS) to keep the list short and modern —
  // only the group containing the current page starts open. Items are
  // gated by permission and only shown once their route actually exists,
  // so modules "light up" as they are built without ever linking dead.
  //   label, route, active (routeIs pattern), icon, color, permission (null = always
  //   visible to authed users; an array = visible when the user holds *any* of them), badge
  $unread = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;

  // Jumlah pengajuan perpanjangan yang menunggu keputusan — hanya relevan
  // (dan hanya dihitung) untuk petugas/admin yang berhak memutuskan.
  $pendingExtensions = auth()->user()?->can('loan-extensions.approve')
    ? App\Models\LoanExtension::where('status', App\Enums\ExtensionStatus::PENDING)->count()
    : 0;

  $general = [
    ['label' => 'Dashboard',    'route' => 'dashboard',           'active' => 'dashboard',       'icon' => 'ni ni-tv-2',    'color' => 'text-blue-500',    'permission' => 'dashboard.view'],
    ['label' => 'Katalog',      'route' => 'catalog.index',       'active' => 'catalog.*',       'icon' => 'ni ni-books',   'color' => 'text-emerald-500'],
    ['label' => 'Anggota',      'route' => 'members.index',       'active' => 'members.*',       'icon' => 'ni ni-circle-08', 'color' => 'text-cyan-500', 'permission' => 'members.view'],
    ['label' => 'Notifikasi',   'route' => 'notifications.index', 'active' => 'notifications.*', 'icon' => 'ni ni-bell-55', 'color' => 'text-yellow-500', 'badge' => $unread],
  ];

  $masterData = [
    ['label' => 'Buku',           'route' => 'books.index',       'active' => 'books.*',        'icon' => 'ni ni-book-bookmark',  'color' => 'text-blue-500',    'permission' => 'books.view'],
    ['label' => 'Eksemplar Buku', 'route' => 'book-copies.index', 'active' => 'book-copies.*',  'icon' => 'ni ni-single-copy-04', 'color' => 'text-orange-500',  'permission' => 'book-copies.view'],
    ['label' => 'Kategori',       'route' => 'categories.index',  'active' => 'categories.*',   'icon' => 'ni ni-tag',            'color' => 'text-emerald-500', 'permission' => 'categories.view'],
    ['label' => 'Rak',            'route' => 'racks.index',       'active' => 'racks.*',        'icon' => 'ni ni-map-big',        'color' => 'text-slate-700',   'permission' => 'racks.view'],
  ];

  $transactions = [
    ['label' => 'Pengajuan',            'route' => 'loans.index',            'active' => 'loans.index',        'icon' => 'ni ni-cart',              'color' => 'text-blue-500',    'permission' => ['loans.view-own', 'loans.view-all']],
    ['label' => 'Menunggu Verifikasi',  'route' => 'loans.pending',          'active' => 'loans.pending',      'icon' => 'ni ni-watch-time',        'color' => 'text-orange-500',  'permission' => 'loans.approve'],
    ['label' => 'Siap Diambil',         'route' => 'loans.ready',            'active' => 'loans.ready',        'icon' => 'ni ni-box-2',             'color' => 'text-cyan-500',    'permission' => 'loans.handover'],
    ['label' => 'Peminjaman Aktif',     'route' => 'loans.active',           'active' => 'loans.active',       'icon' => 'ni ni-single-copy-04',    'color' => 'text-emerald-500', 'permission' => 'loans.view-all'],
    ['label' => 'Pengembalian',         'route' => 'returns.index',          'active' => 'returns.*',          'icon' => 'ni ni-cloud-download-95', 'color' => 'text-violet-500',  'permission' => 'returns.process'],
    ['label' => 'Perpanjangan',         'route' => 'loan-extensions.index',  'active' => 'loan-extensions.*',  'icon' => 'ni ni-calendar-grid-58',  'color' => 'text-yellow-500',  'permission' => ['loan-extensions.view', 'loan-extensions.create'], 'badge' => $pendingExtensions],
    ['label' => 'Keterlambatan',        'route' => 'loans.overdue',          'active' => 'loans.overdue',      'icon' => 'ni ni-time-alarm',        'color' => 'text-red-600',     'permission' => 'loans.view-all'],
  ];

  $reports = [
    ['label' => 'Peminjaman',    'route' => 'reports.loans',      'active' => 'reports.loans',      'icon' => 'ni ni-chart-bar-32', 'color' => 'text-blue-500',    'permission' => 'reports.view'],
    ['label' => 'Pengembalian',  'route' => 'reports.returns',    'active' => 'reports.returns',    'icon' => 'ni ni-chart-bar-32', 'color' => 'text-emerald-500', 'permission' => 'reports.view'],
    ['label' => 'Keterlambatan', 'route' => 'reports.overdue',    'active' => 'reports.overdue',    'icon' => 'ni ni-chart-bar-32', 'color' => 'text-red-600',     'permission' => 'reports.view'],
    ['label' => 'Statistik',     'route' => 'reports.statistics', 'active' => 'reports.statistics', 'icon' => 'ni ni-chart-pie-35', 'color' => 'text-violet-500',  'permission' => 'reports.view'],
  ];

  // Administrasi + Pengaturan + Security merged into one group — all
  // super-admin-oriented, and separate single-item accordions would just
  // add clutter back.
  $administration = [
    ['label' => 'User',               'route' => 'users.index',              'active' => 'users.*',              'icon' => 'ni ni-single-02',         'color' => 'text-blue-500',    'permission' => 'users.view'],
    ['label' => 'Role',               'route' => 'roles.index',              'active' => 'roles.*',              'icon' => 'ni ni-badge',             'color' => 'text-orange-500',  'permission' => 'roles.manage'],
    ['label' => 'Permission',         'route' => 'permissions.index',        'active' => 'permissions.*',        'icon' => 'ni ni-key-25',            'color' => 'text-emerald-500', 'permission' => 'permissions.manage'],
    ['label' => 'Audit Log',          'route' => 'audit-logs.index',         'active' => 'audit-logs.*',         'icon' => 'ni ni-time-alarm',        'color' => 'text-slate-700',   'permission' => 'audit-logs.view'],
    ['label' => 'Pengaturan Aplikasi','route' => 'settings.edit',            'active' => 'settings.edit',        'icon' => 'ni ni-settings-gear-65',  'color' => 'text-cyan-500',    'permission' => 'settings.manage'],
    ['label' => 'Security Dashboard', 'route' => 'security-dashboard.index', 'active' => 'security-dashboard.*', 'icon' => 'ni ni-lock-circle-open',  'color' => 'text-red-600',     'permission' => 'security-dashboard.view'],
  ];

  $visible = function (array $items) {
    return collect($items)->filter(function ($item) {
      if (! Route::has($item['route'])) {
        return false;
      }

      if (empty($item['permission'])) {
        return true;
      }

      return is_array($item['permission'])
        ? (bool) auth()->user()?->canAny($item['permission'])
        : (bool) auth()->user()?->can($item['permission']);
    });
  };

  $linkBase   = 'py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-900';
  $linkActive = 'bg-blue-500/13 rounded-lg font-semibold text-slate-700 dark:text-white';

  $renderItems = function ($items) use ($linkBase, $linkActive) {
    foreach ($items as $item) {
      $isActive = request()->routeIs($item['active']);
      echo '<li class="mt-0.5 w-full">';
      echo '<a class="'.$linkBase.' '.($isActive ? $linkActive : '').'" href="'.route($item['route']).'"'.($isActive ? ' sidenav-active' : '').'>';
      echo '<div class="mr-2 flex h-7 w-7 items-center justify-center rounded-lg bg-center stroke-0 text-center"><i class="relative top-0 text-sm leading-normal '.$item['color'].' '.$item['icon'].'"></i></div>';
      echo '<span class="ml-1 duration-300 opacity-100 pointer-events-none ease">'.e($item['label']).'</span>';
      if (! empty($item['badge'])) {
        echo '<span class="ml-auto px-2 py-0.5 text-xxs font-bold text-white rounded-md bg-gradient-to-tl from-red-600 to-orange-600">'.(int) $item['badge'].'</span>';
      }
      echo '</a></li>';
    }
  };

  $groups = [
    ['title' => 'Master Data', 'icon' => 'ni ni-collection', 'items' => $visible($masterData)],
    ['title' => 'Transaksi', 'icon' => 'ni ni-cart', 'items' => $visible($transactions)],
    ['title' => 'Laporan', 'icon' => 'ni ni-chart-bar-32', 'items' => $visible($reports)],
    ['title' => 'Administrasi', 'icon' => 'ni ni-settings-gear-65', 'items' => $visible($administration)],
  ];
@endphp

<!-- sidenav  -->
<aside sidenav-main class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl dark:shadow-none dark:bg-slate-850 max-w-64 ease-nav-brand z-990 xl:ml-6 rounded-2xl xl:left-0 xl:translate-x-0" aria-expanded="false">
  <div class="h-19">
    <i class="absolute top-0 right-0 p-4 opacity-50 cursor-pointer fas fa-times dark:text-white text-slate-400 xl:hidden" sidenav-close></i>
    <a class="block px-8 py-6 m-0 text-sm whitespace-nowrap dark:text-white text-slate-700" href="{{ route('dashboard') }}">
      @if (setting('logo'))
        <img src="{{ asset('storage/'.setting('logo')) }}" class="inline h-full max-w-full transition-all duration-200 ease-nav-brand max-h-8" alt="{{ app_name() }}" />
      @else
        <img src="{{ asset('assets/img/logo-ct-dark.png') }}" class="inline h-full max-w-full transition-all duration-200 dark:hidden ease-nav-brand max-h-8" alt="main_logo" />
        <img src="{{ asset('assets/img/logo-ct.png') }}" class="hidden h-full max-w-full transition-all duration-200 dark:inline ease-nav-brand max-h-8" alt="main_logo" />
      @endif
      <span class="ml-1 font-semibold transition-all duration-200 ease-nav-brand">{{ app_name() }}</span>
    </a>
  </div>

  <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent" />

  <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
    <ul class="flex flex-col pl-0 mb-0">
      {!! $renderItems($visible($general)) !!}

      @foreach ($groups as $group)
        @if ($group['items']->isNotEmpty())
          @php $isOpen = $group['items']->contains(fn ($item) => request()->routeIs($item['active'])); @endphp
          <li class="w-full mt-1">
            <details class="group" @if ($isOpen) open @endif>
              <summary class="flex items-center justify-between py-2 mx-2 mt-2 mb-0.5 pl-4 pr-4 text-xs font-bold leading-tight uppercase transition-colors rounded-lg cursor-pointer list-none dark:text-white opacity-70 hover:opacity-100 hover:bg-gray-100 dark:hover:bg-slate-900 [&::-webkit-details-marker]:hidden [&::marker]:hidden">
                <span class="flex items-center">
                  <i class="mr-2 text-sm {{ $group['icon'] }}"></i>
                  {{ $group['title'] }}
                </span>
                <i class="text-xs transition-transform duration-200 fas fa-chevron-down group-open:rotate-180"></i>
              </summary>
              <ul class="pl-0 mt-0.5 mb-1">
                {!! $renderItems($group['items']) !!}
              </ul>
            </details>
          </li>
        @endif
      @endforeach

      {{-- Profil & Keluar live in the header profile dropdown now
           (layouts/partials/profile-dropdown.blade.php) — this sidenav is
           only ever rendered behind the 'auth' middleware, so there's no
           need to repeat them here or handle a guest fallback. --}}
    </ul>
  </div>

  <div class="mx-4">
    <!-- load phantom colors for card after: -->
    <p class="invisible hidden text-gray-800 text-red-500 text-red-600 text-blue-500 dark:bg-white bg-slate-500 bg-gray-500/30 bg-cyan-500/30 bg-emerald-500/30 bg-orange-500/30 bg-red-500/30 bg-blue-500/30 after:bg-gradient-to-tl after:from-zinc-800 after:to-zinc-700 dark:bg-gradient-to-tl dark:from-slate-750 dark:to-gray-850 after:from-blue-700 after:to-cyan-500 after:from-orange-500 after:to-yellow-500 after:from-green-600 after:to-lime-400 after:from-red-600 after:to-orange-600 after:from-slate-600 after:to-slate-300 text-emerald-500 text-cyan-500 text-slate-400 text-yellow-500 text-violet-500"></p>
    <div class="p-3 mb-4 text-center rounded-xl bg-gray-50 dark:bg-slate-900" sidenav-card>
      <h6 class="mb-0 text-sm dark:text-white text-slate-700">{{ setting('institution_name') ?: app_name() }}</h6>
      <p class="mb-2 text-xs font-semibold leading-tight dark:text-white dark:opacity-60">Sistem Informasi Peminjaman Buku</p>
    </div>
  </div>
</aside>
<!-- end sidenav -->
