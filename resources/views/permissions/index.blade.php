@extends('layouts.app')

@section('title', 'Permission')
@section('page', 'permissions')

@php
  $groupLabels = [
    'dashboard' => 'Dashboard', 'books' => 'Buku', 'book-copies' => 'Eksemplar Buku',
    'categories' => 'Kategori', 'authors' => 'Penulis', 'publishers' => 'Penerbit', 'racks' => 'Rak',
    'members' => 'Anggota', 'loans' => 'Peminjaman', 'returns' => 'Pengembalian', 'fines' => 'Denda',
    'reports' => 'Laporan', 'users' => 'User', 'roles' => 'Role', 'permissions' => 'Permission',
    'settings' => 'Pengaturan', 'audit-logs' => 'Audit Log', 'security-dashboard' => 'Security Dashboard',
  ];

  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Permission" subtitle="Daftar referensi seluruh permission dan role yang memilikinya. Untuk mengubah, kelola dari halaman Role.">
    @foreach ($groups as $group => $permissions)
      <div class="mb-6 last:mb-0">
        <h6 class="mb-2 text-sm dark:text-white">{{ $groupLabels[$group] ?? ucfirst(str_replace('-', ' ', $group)) }}</h6>
        <div class="p-0 overflow-x-auto border border-solid rounded-xl border-gray-200 dark:border-white/10">
          <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
            <thead class="align-bottom">
              <tr>
                <th class="{{ $th }}">Permission</th>
                <th class="{{ $th }}">Role</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($permissions as $permission)
                <tr>
                  <td class="{{ $td }} text-sm font-semibold dark:text-white">{{ $permission->name }}</td>
                  <td class="{{ $td }} text-sm">
                    @forelse ($permission->roles as $role)
                      <span class="inline-block px-2 py-0.5 mr-1 mb-1 text-xxs font-bold text-white uppercase rounded-md bg-gradient-to-tl from-blue-500 to-violet-500">{{ str_replace('-', ' ', $role->name) }}</span>
                    @empty
                      <span class="text-xs text-slate-400">Belum ada role</span>
                    @endforelse
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endforeach
  </x-card>
@endsection
