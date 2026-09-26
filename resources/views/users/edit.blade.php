@extends('layouts.app')

@section('title', 'Ubah Pengguna')
@section('page', 'users')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 lg:w-8/12 lg:flex-none">
      <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        <x-card>
          <x-slot:actions>
            <x-button variant="outline" href="{{ route('users.index') }}">Batal</x-button>
            <x-button type="submit" icon="fas fa-save">Simpan perubahan</x-button>
          </x-slot:actions>
          <x-slot:title>
            <span class="flex items-center">
              <img src="{{ $user->avatar_url }}" class="inline-flex items-center justify-center mr-3 text-sm text-white h-10 w-10 rounded-xl" alt="{{ $user->name }}" />
              <span>{{ $user->name }}</span>
            </span>
          </x-slot:title>
          @include('users._form')
        </x-card>
      </form>
    </div>

    <div class="w-full max-w-full px-3 mt-6 lg:w-4/12 lg:flex-none lg:mt-0">
      <x-card title="Akun" class="mb-6">
        <ul class="flex flex-col pl-0 mb-0 text-sm rounded-lg">
          <li class="flex justify-between py-2 border-b border-solid border-gray-200 dark:border-white/10"><span class="text-slate-500 dark:text-white/70">Bergabung</span><span class="font-semibold text-slate-700 dark:text-white">{{ $user->created_at->format('d M Y') }}</span></li>
          <li class="flex justify-between py-2 border-b border-solid border-gray-200 dark:border-white/10"><span class="text-slate-500 dark:text-white/70">Login terakhir</span><span class="font-semibold text-slate-700 dark:text-white">{{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</span></li>
          <li class="flex justify-between py-2 border-b border-solid border-gray-200 dark:border-white/10"><span class="text-slate-500 dark:text-white/70">IP terakhir</span><span class="font-semibold text-slate-700 dark:text-white">{{ $user->last_login_ip ?? '—' }}</span></li>
          <li class="flex justify-between py-2 border-b border-solid border-gray-200 dark:border-white/10"><span class="text-slate-500 dark:text-white/70">Email terverifikasi</span><span class="font-semibold text-slate-700 dark:text-white">{{ $user->email_verified_at?->format('d M Y') ?? 'Belum' }}</span></li>
          <li class="flex justify-between py-2"><span class="text-slate-500 dark:text-white/70">Dua langkah (2FA)</span><span class="font-semibold text-slate-700 dark:text-white">{{ $user->hasTwoFactorEnabled() ? 'Aktif' : 'Nonaktif' }}</span></li>
        </ul>
      </x-card>

      <x-card title="Aktivitas Terbaru">
        <ul class="flex flex-col pl-0 mb-0 rounded-lg">
          @forelse ($activities as $activity)
            <li class="relative flex items-center py-2 mb-1 border-0 text-inherit">
              <div class="inline-flex items-center justify-center w-8 h-8 mr-3 text-white rounded-xl bg-gradient-to-tl {{ $activity->color }} shrink-0"><i class="{{ $activity->icon }} text-xxs"></i></div>
              <div class="flex flex-col min-w-0">
                <h6 class="mb-0 text-sm leading-normal truncate text-slate-700 dark:text-white">{{ $activity->description }}</h6>
                <span class="text-xs leading-tight text-slate-400 dark:text-white/80">{{ $activity->created_at->diffForHumans() }}</span>
              </div>
            </li>
          @empty
            <li class="py-2 text-sm text-slate-400">Belum ada aktivitas.</li>
          @endforelse
        </ul>
        @can('audit-logs.view')
          <a href="{{ route('audit-logs.index') }}" class="text-xs font-semibold text-blue-500">Lihat log lengkap</a>
        @endcan
      </x-card>
    </div>
  </div>
@endsection
