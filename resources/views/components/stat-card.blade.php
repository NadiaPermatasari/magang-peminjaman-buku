{{--
  Kartu statistik dashboard. Semua kartu memakai tinggi dan tata letak yang
  sama supaya grid dashboard rata — label dua baris tidak membuat kartu
  tetangganya ikut melar.
  <x-stat-card label="Terlambat" :value="3" icon="ni ni-fat-remove" url="..." />
--}}
@props([
  'label',
  'value',
  'icon' => 'ni ni-app',
  'gradient' => 'from-blue-500 to-violet-500',
  'url' => null,
])

<a
  @if ($url) href="{{ $url }}" @endif
  class="relative flex items-center w-full h-full p-4 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border{{ $url ? ' transition-all cursor-pointer hover:-translate-y-px hover:shadow-2xl' : '' }}"
>
  <div class="flex-1 min-w-0 pr-3">
    <p class="flex items-start mb-1 text-sm font-semibold leading-tight uppercase min-h-10 dark:text-white dark:opacity-60">{{ $label }}</p>
    <h5 class="mb-0 font-bold leading-none dark:text-white">{{ $value }}</h5>
  </div>
  <div class="inline-flex items-center justify-center w-12 h-12 text-center shrink-0 rounded-circle bg-gradient-to-tl {{ $gradient }}">
    <i class="{{ $icon }} text-lg leading-none text-white"></i>
  </div>
</a>
