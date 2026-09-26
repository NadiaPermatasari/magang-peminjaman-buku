@extends('layouts.app')

@section('title', 'Pengguna')
@section('page', 'users')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Pengguna" :subtitle="$users->total().' akun'" :padding="false">
    <x-slot:actions>
      <div class="flex items-center p-1 mr-2 bg-gray-100 rounded-lg dark:bg-slate-700">
        <a href="{{ route('users.index', array_filter(['search' => request('search')])) }}" class="px-3 py-1 text-xs font-bold rounded-md {{ ! request('role') ? 'text-white bg-blue-500 shadow-md' : 'text-slate-500 dark:text-white' }}">Semua</a>
        @foreach ($roles as $value => $label)
          <a href="{{ route('users.index', array_filter(['role' => $value, 'search' => request('search')])) }}" class="px-3 py-1 text-xs font-bold rounded-md {{ request('role') === $value ? 'text-white bg-blue-500 shadow-md' : 'text-slate-500 dark:text-white' }}">{{ $label }}</a>
        @endforeach
      </div>
      <form method="GET" action="{{ route('users.index') }}" class="hidden sm:block">
        <input type="hidden" name="role" value="{{ request('role') }}" />
        <div class="relative flex flex-wrap items-stretch w-full transition-all rounded-lg ease">
          <span class="text-sm ease leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all">
            <i class="fas fa-search"></i>
          </span>
          <input type="text" name="search" value="{{ request('search') }}" class="pl-9 text-sm focus:shadow-primary-outline ease leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 dark:bg-slate-850 dark:text-white bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow" placeholder="Cari nama, email..." />
        </div>
      </form>
      @can('create', App\Models\User::class)
        <x-button href="{{ route('users.create') }}" icon="fas fa-plus">Tambah pengguna</x-button>
      @endcan
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Pengguna</th>
            <th class="{{ $th }} text-center">Role</th>
            <th class="{{ $th }} text-center">Status</th>
            <th class="{{ $th }} text-center">Login terakhir</th>
            <th class="{{ $th }} text-center">Bergabung</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td class="{{ $td }}">
                <div class="flex px-2 py-1">
                  <div>
                    <img src="{{ $user->avatar_url }}" class="inline-flex items-center justify-center mr-4 text-sm text-white transition-all duration-200 ease-in-out h-9 w-9 rounded-xl" alt="{{ $user->name }}" />
                  </div>
                  <div class="flex flex-col justify-center">
                    <h6 class="mb-0 text-sm leading-normal dark:text-white">{{ $user->name }} @if ($user->id === auth()->id())<span class="text-xs text-slate-400">(Anda)</span>@endif</h6>
                    <p class="mb-0 text-xs leading-tight dark:text-white dark:opacity-80 text-slate-400">{{ $user->email }}</p>
                  </div>
                </div>
              </td>
              <td class="{{ $td }} text-sm leading-normal text-center">
                @foreach ($user->getRoleNames() as $roleName)
                  <span class="bg-gradient-to-tl from-blue-500 to-violet-500 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ str_replace('-', ' ', $roleName) }}</span>
                @endforeach
              </td>
              <td class="{{ $td }} text-sm leading-normal text-center">
                @if ($user->email_verified_at)
                  <span class="bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Terverifikasi</span>
                @else
                  <span class="bg-gradient-to-tl from-orange-500 to-yellow-500 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Menunggu</span>
                @endif
                @if ($user->hasTwoFactorEnabled())
                  <i class="ml-1 text-xs text-emerald-500 fas fa-shield-alt" title="2FA aktif"></i>
                @endif
              </td>
              <td class="{{ $td }} text-center">
                <span class="text-xs font-semibold leading-tight dark:text-white dark:opacity-80 text-slate-400">{{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</span>
              </td>
              <td class="{{ $td }} text-center">
                <span class="text-xs font-semibold leading-tight dark:text-white dark:opacity-80 text-slate-400">{{ $user->created_at->format('d/m/y') }}</span>
              </td>
              <td class="{{ $td }} text-center">
                @can('update', $user)
                  <a href="{{ route('users.edit', $user) }}" class="mr-3 text-xs font-semibold leading-tight dark:text-white dark:opacity-80 text-slate-400 hover:text-blue-500"><i class="mr-1 fas fa-pen"></i>Ubah</a>
                @endcan
                @can('delete', $user)
                  @if ($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.disable', $user) }}" class="inline" onsubmit="return confirm('Nonaktifkan {{ addslashes($user->name) }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="p-0 text-xs font-semibold leading-tight bg-transparent border-0 cursor-pointer dark:text-white dark:opacity-80 text-slate-400 hover:text-red-600"><i class="mr-1 fas fa-ban"></i>Nonaktifkan</button>
                    </form>
                  @endif
                @endcan
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="p-6 text-sm text-center dark:text-white/80">Tidak ada pengguna ditemukan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($users->hasPages())
      <div class="px-6 pt-4">
        {{ $users->links('pagination.argon') }}
      </div>
    @endif
  </x-card>
@endsection
