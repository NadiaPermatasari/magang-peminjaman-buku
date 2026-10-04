@extends('layouts.app')

@section('title', 'Dashboard')
@section('page', 'dashboard')

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
    <div class="flex flex-wrap -mx-3 mb-3">
      @foreach ([
        ['label' => 'Sedang Dipinjam', 'value' => $memberWidgets['borrowedCount'], 'icon' => 'ni ni-single-copy-04', 'gradient' => 'from-blue-500 to-violet-500', 'url' => route('loans.index', ['status' => \App\Enums\LoanStatus::BORROWED->value])],
        ['label' => 'Pengajuan Pending', 'value' => $memberWidgets['pendingCount'], 'icon' => 'ni ni-watch-time', 'gradient' => 'from-orange-500 to-yellow-500', 'url' => route('loans.index', ['status' => \App\Enums\LoanStatus::PENDING->value])],
        ['label' => 'Hampir Jatuh Tempo', 'value' => $memberWidgets['dueSoonCount'], 'icon' => 'ni ni-time-alarm', 'gradient' => 'from-cyan-500 to-blue-500', 'url' => route('loans.index', ['status' => \App\Enums\LoanStatus::BORROWED->value])],
        ['label' => 'Terlambat', 'value' => $memberWidgets['overdueCount'], 'icon' => 'ni ni-fat-remove', 'gradient' => 'from-red-600 to-orange-600', 'url' => route('loans.index', ['status' => \App\Enums\LoanStatus::OVERDUE->value])],
        ['label' => 'Perpanjangan Menunggu', 'value' => $memberWidgets['pendingExtensionCount'], 'icon' => 'ni ni-calendar-grid-58', 'gradient' => 'from-slate-700 to-slate-500', 'url' => route('loan-extensions.index')],
      ] as $card)
        <div class="flex w-full max-w-full px-3 mb-6 sm:w-1/2 xl:w-1/3">
          <x-stat-card :label="$card['label']" :value="$card['value']" :icon="$card['icon']" :gradient="$card['gradient']" :url="$card['url']" />
        </div>
      @endforeach
    </div>
  @endif

  @if (filled($staffCards))
    <div class="flex flex-wrap -mx-3 mb-3">
      @foreach ($staffCards as $card)
        <div class="flex w-full max-w-full px-3 mb-6 sm:w-1/2 xl:w-1/3">
          <x-stat-card :label="$card['label']" :value="$card['value']" :icon="$card['icon']" :gradient="$card['gradient']" :url="$card['url']" />
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
