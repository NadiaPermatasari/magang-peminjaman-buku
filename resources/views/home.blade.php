@extends('layouts.public')

@section('title', setting('landing_hero_title') ?: app_name())
@section('meta-description', setting('landing_meta_description') ?: setting('description') ?: (setting('landing_hero_subtitle') ?: app_name()))
@section('canonical', route('home'))
@section('page', 'home')

@push('structured-data')
  <script type="application/ld+json">
    {!! json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'Library',
      'name' => setting('institution_name') ?: app_name(),
      'url' => route('home'),
      'description' => setting('description') ?: setting('landing_hero_subtitle'),
      'address' => setting('address'),
      'telephone' => setting('phone'),
      'email' => setting('email'),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
  </script>
@endpush

@section('content')
  {{-- Hero — Argon-style colored header with a wave divider into the white content below --}}
  <section class="relative pt-16 pb-40 overflow-hidden bg-gradient-to-tl from-blue-500 to-violet-500 lg:pb-52">
    <div class="absolute inset-0 bg-[url('/assets/img/shapes/pattern-lines.svg')] bg-cover opacity-10"></div>

    <div class="relative z-10 flex flex-wrap items-center max-w-6xl px-6 mx-auto">
      <div class="w-full lg:w-6/12">
        <span class="inline-block px-3 py-1 mb-5 text-xs font-bold tracking-wide text-white uppercase border rounded-full border-white/30 bg-white/10 backdrop-blur">
          Sistem Perpustakaan Digital
        </span>
        <h1 class="mb-5 text-4xl font-bold leading-tight text-white lg:text-5xl">{{ setting('landing_hero_title') ?: app_name() }}</h1>
        <p class="mb-8 text-lg leading-relaxed text-white/85">{{ setting('landing_hero_subtitle') }}</p>
        <div class="flex flex-wrap gap-3">
          @auth
            <x-button href="{{ route('dashboard') }}" variant="light" size="lg" icon="fas fa-th-large">Ke Dashboard</x-button>
          @else
            <x-button href="{{ route('login') }}" variant="light" size="lg" icon="fas fa-sign-in-alt">Masuk ke Sistem</x-button>
          @endauth
          <x-button href="#fitur" variant="outline" size="lg" class="!text-white !border-white/60 hover:!bg-white/10">Pelajari Lebih Lanjut</x-button>
        </div>
      </div>
      <div class="flex justify-center w-full mt-14 lg:mt-0 lg:w-6/12 lg:justify-end">
        <div class="relative">
          <div class="absolute rounded-full -inset-10 bg-white/10 blur-3xl"></div>
          <img src="{{ setting('landing_hero_image') ? asset('storage/'.setting('landing_hero_image')) : asset('assets/img/illustrations/icon-documentation.svg') }}" alt="Ilustrasi {{ app_name() }}" class="relative max-w-[16rem] lg:max-w-sm drop-shadow-2xl" loading="eager" />
        </div>
      </div>
    </div>

    <img src="{{ asset('assets/img/shapes/wave-down.svg') }}" alt="" aria-hidden="true" class="absolute bottom-0 left-0 w-full leading-none pointer-events-none" />
  </section>

  {{-- Stat kartu mengambang, menindih batas gelombang — pola khas Argon --}}
  <section class="relative z-20 px-6 -mt-24 lg:-mt-28">
    <div class="grid max-w-5xl grid-cols-3 gap-2 p-6 mx-auto bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl sm:gap-6 sm:p-8">
      @foreach ([
        ['icon' => 'ni ni-book-bookmark', 'value' => $stats['totalBooks'], 'label' => 'Judul Buku'],
        ['icon' => 'ni ni-tag', 'value' => $stats['totalCategories'], 'label' => 'Kategori'],
        ['icon' => 'ni ni-circle-08', 'value' => $stats['totalMembers'], 'label' => 'Anggota Terdaftar'],
      ] as $i => $stat)
        <div class="flex flex-col items-center px-2 text-center {{ $i > 0 ? 'border-l border-solid border-gray-100 dark:border-white/10' : '' }}">
          <div class="inline-flex items-center justify-center w-10 h-10 mb-2 text-white rounded-xl bg-gradient-to-tl from-blue-500 to-violet-500 sm:w-12 sm:h-12">
            <i class="{{ $stat['icon'] }} text-sm sm:text-lg"></i>
          </div>
          <h3 class="mb-0 text-xl font-bold sm:text-3xl text-slate-700 dark:text-white">{{ number_format($stat['value']) }}+</h3>
          <p class="mb-0 text-xs sm:text-sm text-slate-400">{{ $stat['label'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- Fitur --}}
  <section id="fitur" class="max-w-6xl px-6 pt-20 pb-12 mx-auto">
    <div class="max-w-xl mx-auto mb-12 text-center">
      <span class="text-xs font-bold tracking-wide text-blue-500 uppercase">Fitur Unggulan</span>
      <h2 class="mt-2 text-2xl font-bold lg:text-3xl text-slate-700 dark:text-white">Kenapa Memilih Sistem Kami?</h2>
    </div>
    <div class="flex flex-wrap -mx-3">
      @foreach ([
        ['icon' => 'ni ni-books', 'title' => 'Katalog Digital', 'desc' => 'Cari dan telusuri koleksi buku kapan saja secara online.'],
        ['icon' => 'ni ni-cart', 'title' => 'Peminjaman Online', 'desc' => 'Ajukan peminjaman tanpa antre, dipantau petugas secara real-time.'],
        ['icon' => 'ni ni-bell-55', 'title' => 'Notifikasi Otomatis', 'desc' => 'Pengingat jatuh tempo dan status pengajuan langsung ke akun Anda.'],
        ['icon' => 'ni ni-lock-circle-open', 'title' => 'Aman & Terpercaya', 'desc' => 'Data pribadi terenkripsi dengan kontrol akses berlapis.'],
      ] as $feature)
        <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 lg:w-1/4">
          <div class="h-full p-6 text-center transition-all duration-300 bg-white shadow-lg dark:bg-slate-850 rounded-2xl hover:shadow-2xl hover:-translate-y-1">
            <div class="inline-flex items-center justify-center w-14 h-14 mb-4 text-white shadow-md rounded-xl bg-gradient-to-tl from-blue-500 to-violet-500">
              <i class="{{ $feature['icon'] }} text-xl"></i>
            </div>
            <h6 class="mb-2 dark:text-white">{{ $feature['title'] }}</h6>
            <p class="mb-0 text-sm text-slate-500 dark:text-white/70">{{ $feature['desc'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  {{-- Koleksi Terbaru --}}
  @if ($featuredBooks->isNotEmpty())
    <section class="py-12 bg-gray-50 dark:bg-slate-900">
      <div class="max-w-6xl px-6 mx-auto">
        <div class="max-w-xl mx-auto mb-10 text-center">
          <span class="text-xs font-bold tracking-wide text-blue-500 uppercase">Perpustakaan Kami</span>
          <h2 class="mt-2 text-2xl font-bold lg:text-3xl text-slate-700 dark:text-white">Koleksi Terbaru</h2>
        </div>
        <div class="flex flex-wrap -mx-3">
          @foreach ($featuredBooks as $book)
            <div class="w-1/2 max-w-full px-3 mb-6 sm:w-1/4 lg:w-1/6">
              <a href="{{ route('login') }}" class="block group">
                <div class="mb-2 overflow-hidden shadow-lg rounded-xl">
                  <img src="{{ $book->cover_url ?? asset('assets/img/theme/bootstrap.jpg') }}" alt="Sampul buku {{ $book->title }}" class="object-cover w-full transition-transform duration-300 h-36 group-hover:scale-105" loading="lazy" />
                </div>
                <p class="mb-0 text-xs font-semibold leading-tight truncate text-slate-700 dark:text-white group-hover:text-blue-500">{{ $book->title }}</p>
                <p class="text-xxs text-slate-400">{{ $book->category?->name }}</p>
              </a>
            </div>
          @endforeach
        </div>
        <p class="mt-4 text-sm text-center text-slate-400">Masuk untuk melihat katalog lengkap dan mengajukan peminjaman.</p>
      </div>
    </section>
  @endif

  {{-- Tentang --}}
  @if (setting('landing_about_content'))
    <section class="max-w-6xl px-6 py-20 mx-auto">
      <div class="flex flex-wrap items-center -mx-3">
        <div class="w-full max-w-full px-3 mb-10 lg:w-5/12 lg:mb-0">
          <div class="relative">
            <div class="absolute rounded-2xl -inset-3 bg-gradient-to-tl from-blue-500/10 to-violet-500/10"></div>
            <img src="{{ asset('assets/img/illustrations/rocket-white.png') }}" alt="Tentang {{ app_name() }}" class="relative w-full max-w-xs mx-auto lg:max-w-none" loading="lazy" />
          </div>
        </div>
        <div class="w-full max-w-full px-3 lg:w-7/12">
          <span class="text-xs font-bold tracking-wide text-blue-500 uppercase">Tentang Kami</span>
          <h2 class="mt-2 mb-4 text-2xl font-bold lg:text-3xl text-slate-700 dark:text-white">{{ setting('landing_about_title') }}</h2>
          <p class="leading-relaxed text-slate-500 dark:text-white/70">{{ setting('landing_about_content') }}</p>
        </div>
      </div>
    </section>
  @endif

  {{-- CTA penutup --}}
  <section class="px-6 pb-20">
    <div class="max-w-4xl p-10 mx-auto text-center shadow-xl bg-gradient-to-tl from-blue-500 to-violet-500 rounded-3xl lg:p-14">
      <h2 class="mb-3 text-2xl font-bold text-white lg:text-3xl">Siap mulai menjelajahi koleksi kami?</h2>
      <p class="max-w-lg mx-auto mb-8 text-white/85">Masuk sekarang untuk melihat katalog lengkap, mengajukan peminjaman, dan memantau riwayat Anda.</p>
      @auth
        <x-button href="{{ route('dashboard') }}" variant="light" size="lg" icon="fas fa-th-large">Ke Dashboard</x-button>
      @else
        <x-button href="{{ route('login') }}" variant="light" size="lg" icon="fas fa-sign-in-alt">Masuk ke Sistem</x-button>
      @endauth
    </div>
  </section>
@endsection
