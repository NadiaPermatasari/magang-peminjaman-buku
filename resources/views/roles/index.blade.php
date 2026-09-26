@extends('layouts.app')

@section('title', 'Role')
@section('page', 'roles')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Role" :subtitle="$roles->count().' role terdaftar'" :padding="false">
    <x-slot:actions>
      <x-button href="{{ route('roles.create') }}" icon="fas fa-plus">Tambah Role</x-button>
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Role</th>
            <th class="{{ $th }} text-center">Jumlah Permission</th>
            <th class="{{ $th }} text-center">Jumlah Pengguna</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($roles as $role)
            <tr>
              <td class="{{ $td }} text-sm font-semibold dark:text-white">
                {{ str_replace('-', ' ', $role->name) }}
                @if (in_array($role->name, $protectedRoles, true))
                  <span class="ml-1 px-2 py-0.5 text-xxs font-bold text-white uppercase rounded-md bg-gradient-to-tl from-slate-600 to-slate-300">Bawaan</span>
                @endif
              </td>
              <td class="{{ $td }} text-center text-sm">{{ $role->permissions_count }}</td>
              <td class="{{ $td }} text-center text-sm">{{ $role->users_count }}</td>
              <td class="{{ $td }} text-center">
                <a href="{{ route('roles.edit', $role) }}" class="mr-3 text-xs font-semibold text-slate-400 hover:text-blue-500"><i class="mr-1 fas fa-pen"></i>{{ $role->name === 'super-admin' ? 'Lihat' : 'Ubah' }}</a>
                @unless (in_array($role->name, $protectedRoles, true))
                  <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Hapus role {{ addslashes($role->name) }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-0 text-xs font-semibold bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600"><i class="mr-1 fas fa-trash"></i>Hapus</button>
                  </form>
                @endunless
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-6 text-sm text-center dark:text-white/80">Belum ada role.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-card>
@endsection
