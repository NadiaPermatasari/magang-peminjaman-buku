@extends('layouts.app')

@section('title', 'Dashboard')
@section('page', 'dashboard')

@php
  $quickLinks = collect([
    ['label' => 'Katalog Buku', 'route' => 'catalog.index', 'icon' => 'ni ni-books', 'gradient' => 'from-blue-500 to-violet-500', 'permission' => null],
    ['label' => 'Ajukan Peminjaman', 'route' => 'loans.create', 'icon' => 'ni ni-cart', 'gradient' => 'from-emerald-500 to-teal-400', 'permission' => 'loans.create'],
    ['label' => 'Verifikasi Peminjaman', 'route' => 'loans.pending', 'icon' => 'ni ni-watch-time', 'gradient' => 'from-orange-500 to-yellow-500', 'permission' => 'loans.approve'],
    ['label' => 'Proses Pengembalian', 'route' => 'returns.index', 'icon' => 'ni ni-scanner', 'gradient' => 'from-cyan-500 to-blue-500', 'permission' => 'returns.process'],
    ['label' => 'Kelola Anggota', 'route' => 'members.index', 'icon' => 'ni ni-circle-08', 'gradient' => 'from-red-600 to-orange-600', 'permission' => 'members.view'],
    ['label' => 'Laporan', 'route' => 'reports.statistics', 'icon' => 'ni ni-chart-bar-32', 'gradient' => 'from-slate-700 to-slate-500', 'permission' => 'reports.view'],
  ])->filter(fn ($link) => Route::has($link['route']) && (! $link['permission'] || auth()->user()->can($link['permission'])));
@endphp

@section('content')
  <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
    <div class="flex-auto p-6">
      <h5 class="mb-1 dark:text-white">Selamat datang, {{ auth()->user()->name }} 👋</h5>
      <p class="mb-0 text-sm dark:text-white dark:opacity-60">
        Anda memiliki <span class="font-semibold text-blue-500">{{ $unreadNotifications }}</span> notifikasi belum dibaca.
        <a href="{{ route('notifications.index') }}" class="font-semibold text-blue-500">Lihat semua</a>
      </p>
    </div>
  </div>

  @if ($memberWidgets)
    <div class="flex flex-wrap -mx-3 mb-6">
      @foreach ([
        ['label' => 'Sedang Dipinjam', 'value' => $memberWidgets['borrowedCount'], 'icon' => 'ni ni-single-copy-04', 'gradient' => 'from-blue-500 to-violet-500'],
        ['label' => 'Pengajuan Pending', 'value' => $memberWidgets['pendingCount'], 'icon' => 'ni ni-watch-time', 'gradient' => 'from-orange-500 to-yellow-500'],
        ['label' => 'Hampir Jatuh Tempo', 'value' => $memberWidgets['dueSoonCount'], 'icon' => 'ni ni-time-alarm', 'gradient' => 'from-cyan-500 to-blue-500'],
        ['label' => 'Terlambat', 'value' => $memberWidgets['overdueCount'], 'icon' => 'ni ni-fat-remove', 'gradient' => 'from-red-600 to-orange-600'],
      ] as $card)
        <div class="w-full max-w-full px-3 mb-3 sm:w-1/2 xl:w-1/4">
          <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
            <div class="flex-auto p-4">
              <div class="flex flex-row -mx-3">
                <div class="flex-none w-2/3 max-w-full px-3">
                  <p class="mb-0 text-sm font-semibold uppercase dark:text-white dark:opacity-60">{{ $card['label'] }}</p>
                  <h5 class="mb-0 font-bold dark:text-white">{{ $card['value'] }}</h5>
                </div>
                <div class="px-3 text-right basis-1/3">
                  <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl {{ $card['gradient'] }}"><i class="ni leading-none {{ $card['icon'] }} text-lg relative top-3.5 text-white"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
      @if ($memberWidgets['pendingExtensionCount'] > 0)
        <div class="w-full px-3">
          <div class="p-3 text-sm text-white rounded-lg bg-gradient-to-tl from-orange-500 to-yellow-500">
            <i class="mr-1 fas fa-hourglass-half"></i> Anda memiliki <strong>{{ $memberWidgets['pendingExtensionCount'] }}</strong> pengajuan perpanjangan yang menunggu persetujuan petugas.
            <a href="{{ route('loan-extensions.index') }}" class="font-bold underline">Lihat pengajuan</a>
          </div>
        </div>
      @endif
    </div>
  @endif

  @if ($staffWidgets)
    <div class="flex flex-wrap -mx-3 mb-6">
      @foreach ([
        ['label' => 'Menunggu Verifikasi', 'value' => $staffWidgets['pendingCount'], 'icon' => 'ni ni-watch-time', 'gradient' => 'from-orange-500 to-yellow-500'],
        ['label' => 'Siap Diambil', 'value' => $staffWidgets['readyCount'], 'icon' => 'ni ni-box-2', 'gradient' => 'from-cyan-500 to-blue-500'],
        ['label' => 'Jatuh Tempo Hari Ini', 'value' => $staffWidgets['dueTodayCount'], 'icon' => 'ni ni-time-alarm', 'gradient' => 'from-blue-500 to-violet-500'],
        ['label' => 'Terlambat', 'value' => $staffWidgets['overdueCount'], 'icon' => 'ni ni-fat-remove', 'gradient' => 'from-red-600 to-orange-600'],
        ['label' => 'Dikembalikan Hari Ini', 'value' => $staffWidgets['returnedTodayCount'], 'icon' => 'ni ni-check-bold', 'gradient' => 'from-emerald-500 to-teal-400'],
        ['label' => 'Perpanjangan Menunggu', 'value' => $staffWidgets['pendingExtensionCount'], 'icon' => 'ni ni-calendar-grid-58', 'gradient' => 'from-slate-700 to-slate-500'],
      ] as $card)
        <div class="w-full max-w-full px-3 mb-3 sm:w-1/2 xl:w-1/3">
          <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
            <div class="flex-auto p-4">
              <div class="flex flex-row -mx-3">
                <div class="flex-none w-2/3 max-w-full px-3">
                  <p class="mb-0 text-sm font-semibold uppercase dark:text-white dark:opacity-60">{{ $card['label'] }}</p>
                  <h5 class="mb-0 font-bold dark:text-white">{{ $card['value'] }}</h5>
                </div>
                <div class="px-3 text-right basis-1/3">
                  <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl {{ $card['gradient'] }}"><i class="ni leading-none {{ $card['icon'] }} text-lg relative top-3.5 text-white"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  @if ($adminWidgets)
    <div class="flex flex-wrap -mx-3 mb-6">
      @foreach ([
        ['label' => 'Total Judul', 'value' => $adminWidgets['totalTitles'], 'icon' => 'ni ni-book-bookmark', 'gradient' => 'from-blue-500 to-violet-500'],
        ['label' => 'Total Eksemplar', 'value' => $adminWidgets['totalCopies'], 'icon' => 'ni ni-single-copy-04', 'gradient' => 'from-cyan-500 to-blue-500'],
        ['label' => 'Total Anggota', 'value' => $adminWidgets['totalMembers'], 'icon' => 'ni ni-circle-08', 'gradient' => 'from-emerald-500 to-teal-400'],
        ['label' => 'Peminjaman Aktif', 'value' => $adminWidgets['activeLoans'], 'icon' => 'ni ni-cart', 'gradient' => 'from-orange-500 to-yellow-500'],
        ['label' => 'Pengajuan Pending', 'value' => $adminWidgets['pendingLoans'], 'icon' => 'ni ni-watch-time', 'gradient' => 'from-orange-500 to-yellow-500'],
        ['label' => 'Terlambat', 'value' => $adminWidgets['overdueLoans'], 'icon' => 'ni ni-fat-remove', 'gradient' => 'from-red-600 to-orange-600'],
        ['label' => 'Dikembalikan Bulan Ini', 'value' => $adminWidgets['returnedThisMonth'], 'icon' => 'ni ni-check-bold', 'gradient' => 'from-emerald-500 to-teal-400'],
        ['label' => 'Perpanjangan Menunggu', 'value' => $adminWidgets['pendingExtensionCount'], 'icon' => 'ni ni-calendar-grid-58', 'gradient' => 'from-slate-700 to-slate-500'],
      ] as $card)
        <div class="w-full max-w-full px-3 mb-3 sm:w-1/2 xl:w-1/4">
          <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
            <div class="flex-auto p-4">
              <div class="flex flex-row -mx-3">
                <div class="flex-none w-2/3 max-w-full px-3">
                  <p class="mb-0 text-sm font-semibold uppercase dark:text-white dark:opacity-60">{{ $card['label'] }}</p>
                  <h5 class="mb-0 font-bold dark:text-white">{{ $card['value'] }}</h5>
                </div>
                <div class="px-3 text-right basis-1/3">
                  <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl {{ $card['gradient'] }}"><i class="ni leading-none {{ $card['icon'] }} text-lg relative top-3.5 text-white"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="flex flex-wrap -mx-3 mb-6">
      <div class="w-full max-w-full px-3 mb-6 lg:w-6/12 lg:flex-none">
        <x-card title="Kategori Populer" subtitle="Berdasarkan jumlah peminjaman">
          <ul class="pl-0 mb-0">
            @forelse ($adminWidgets['popularCategories'] as $row)
              <li class="flex items-center justify-between py-1.5 border-b border-solid last:border-0 border-gray-100 dark:border-white/5">
                <span class="text-sm dark:text-white">{{ $row->name }}</span>
                <span class="text-xs font-semibold text-slate-400">{{ $row->total }} peminjaman</span>
              </li>
            @empty
              <li class="py-2 text-sm text-slate-400">Belum ada data.</li>
            @endforelse
          </ul>
        </x-card>
      </div>
      <div class="w-full max-w-full px-3 mb-6 lg:w-6/12 lg:flex-none">
        <x-card title="Buku Populer" subtitle="Berdasarkan jumlah peminjaman">
          <ul class="pl-0 mb-0">
            @forelse ($adminWidgets['popularBooks'] as $row)
              <li class="flex items-center justify-between py-1.5 border-b border-solid last:border-0 border-gray-100 dark:border-white/5">
                <span class="text-sm dark:text-white">{{ $row->title }}</span>
                <span class="text-xs font-semibold text-slate-400">{{ $row->total }} peminjaman</span>
              </li>
            @empty
              <li class="py-2 text-sm text-slate-400">Belum ada data.</li>
            @endforelse
          </ul>
        </x-card>
      </div>
    </div>
  @endif

  @if ($quickLinks->isNotEmpty())
    <div class="flex flex-wrap -mx-3 mb-6">
      @foreach ($quickLinks as $link)
        <div class="w-full max-w-full px-3 mb-3 sm:w-1/2 xl:w-1/3">
          <a href="{{ route($link['route']) }}" class="relative flex items-center p-4 min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border hover:-translate-y-px transition-all">
            <div class="inline-flex items-center justify-center w-12 h-12 mr-3 text-center rounded-xl bg-gradient-to-tl {{ $link['gradient'] }} shrink-0">
              <i class="ni {{ $link['icon'] }} text-lg text-white"></i>
            </div>
            <h6 class="mb-0 dark:text-white">{{ $link['label'] }}</h6>
          </a>
        </div>
      @endforeach
    </div>
  @endif

  <div class="relative flex flex-col min-w-0 break-words bg-white border-0 border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl border-black-125 rounded-2xl bg-clip-border">
    <div class="flex justify-between p-4 pb-0 rounded-t-4">
      <h6 class="mb-0 dark:text-white">Aktivitas Terbaru</h6>
      @can('audit-logs.view')
        <a href="{{ route('audit-logs.index') }}" class="text-xs font-semibold text-blue-500">Lihat semua</a>
      @endcan
    </div>
    <div class="flex-auto p-4">
      <ul class="flex flex-col pl-0 mb-0 rounded-lg">
        @forelse ($recentActivities as $activity)
          <li class="relative flex justify-between py-2 pr-4 mb-2 border-0 rounded-xl text-inherit">
            <div class="flex items-center">
              <div class="inline-flex items-center justify-center w-8 h-8 mr-4 text-center text-white bg-center shadow-sm bg-gradient-to-tl {{ $activity->color }} rounded-xl shrink-0">
                <i class="{{ $activity->icon }} text-xxs"></i>
              </div>
              <div class="flex flex-col">
                <h6 class="mb-1 text-sm leading-normal text-slate-700 dark:text-white">{{ $activity->user?->name ?? 'Sistem' }} <span class="font-normal text-slate-500 dark:text-white/70">— {{ $activity->description }}</span></h6>
                <span class="text-xs leading-tight dark:text-white/80">{{ $activity->created_at->diffForHumans() }} · <span class="font-semibold">{{ $activity->action_label }}</span></span>
              </div>
            </div>
          </li>
        @empty
          <li class="py-4 text-sm text-center text-slate-400">Belum ada aktivitas.</li>
        @endforelse
      </ul>
    </div>
  </div>
@endsection
