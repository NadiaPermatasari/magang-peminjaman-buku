@extends('layouts.app')

@section('title', 'Security Dashboard')
@section('page', 'security')

@php
  $cards = [
    ['label' => 'Login Berhasil Hari Ini', 'value' => $stats['loginSuccessToday'], 'icon' => 'ni ni-key-25', 'gradient' => 'from-emerald-500 to-teal-400'],
    ['label' => 'Login Gagal Hari Ini', 'value' => $stats['loginFailedToday'], 'icon' => 'ni ni-fat-remove', 'gradient' => 'from-red-600 to-orange-600'],
    ['label' => '2FA Gagal Hari Ini', 'value' => $stats['twoFactorFailedToday'], 'icon' => 'ni ni-lock-circle-open', 'gradient' => 'from-orange-500 to-yellow-500'],
    ['label' => 'Sesi Aktif (15 menit)', 'value' => $stats['activeSessions'] ?? '—', 'icon' => 'ni ni-world', 'gradient' => 'from-blue-500 to-violet-500'],
  ];
@endphp

@section('content')
  <div class="flex flex-wrap -mx-3 mb-6">
    @foreach ($cards as $card)
      <div class="w-full max-w-full px-3 mb-3 sm:w-1/2 xl:w-1/4">
        <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
          <div class="flex-auto p-4">
            <div class="flex flex-row -mx-3">
              <div class="flex-none w-2/3 max-w-full px-3">
                <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">{{ $card['label'] }}</p>
                <h5 class="mb-0 font-bold dark:text-white">{{ $card['value'] }}</h5>
              </div>
              <div class="px-3 text-right basis-1/3">
                <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl {{ $card['gradient'] }}">
                  <i class="ni leading-none {{ $card['icon'] }} text-lg relative top-3.5 text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mb-6 lg:w-6/12 lg:flex-none">
      <x-card title="Login Gagal Terbaru">
        <ul class="flex flex-col pl-0 mb-0 rounded-lg">
          @forelse ($recentFailedLogins as $log)
            <li class="relative flex items-center py-2 mb-1 border-0 text-inherit">
              <div class="inline-flex items-center justify-center w-8 h-8 mr-3 text-white rounded-xl bg-gradient-to-tl from-red-600 to-orange-600 shrink-0"><i class="{{ $log->icon }} text-xxs"></i></div>
              <div class="flex flex-col min-w-0">
                <h6 class="mb-0 text-sm leading-normal truncate text-slate-700 dark:text-white">{{ $log->description }}</h6>
                <span class="text-xs leading-tight text-slate-400 dark:text-white/80">{{ $log->ip_address }} · {{ $log->created_at->diffForHumans() }}</span>
              </div>
            </li>
          @empty
            <li class="py-2 text-sm text-slate-400">Tidak ada percobaan login gagal terbaru.</li>
          @endforelse
        </ul>
      </x-card>
    </div>

    <div class="w-full max-w-full px-3 mb-6 lg:w-6/12 lg:flex-none">
      <x-card title="Peristiwa Keamanan Terbaru">
        <ul class="flex flex-col pl-0 mb-0 rounded-lg">
          @forelse ($recentSecurityEvents as $log)
            <li class="relative flex items-center py-2 mb-1 border-0 text-inherit">
              <div class="inline-flex items-center justify-center w-8 h-8 mr-3 text-white rounded-xl bg-gradient-to-tl {{ $log->color }} shrink-0"><i class="{{ $log->icon }} text-xxs"></i></div>
              <div class="flex flex-col min-w-0">
                <h6 class="mb-0 text-sm leading-normal truncate text-slate-700 dark:text-white">{{ $log->user?->name ?? 'Sistem' }} — {{ $log->description }}</h6>
                <span class="text-xs leading-tight text-slate-400 dark:text-white/80">{{ $log->created_at->diffForHumans() }}</span>
              </div>
            </li>
          @empty
            <li class="py-2 text-sm text-slate-400">Belum ada peristiwa keamanan.</li>
          @endforelse
        </ul>
      </x-card>
    </div>
  </div>
@endsection
