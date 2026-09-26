@extends('layouts.app')

@section('title', 'Anggota')
@section('page', 'members')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Anggota" :subtitle="$members->total().' anggota'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('members.index') }}" class="flex flex-wrap items-center gap-2">
        <select name="status" onchange="this.form.submit()" class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20">
          <option value="">Semua status</option>
          @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
          @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/no. anggota..." class="text-sm rounded-lg border border-solid border-gray-300 px-3 py-2 dark:bg-slate-850 dark:text-white dark:border-white/20" />
      </form>
      @can('create', App\Models\Member::class)
        <x-button href="{{ route('members.create') }}" icon="fas fa-plus">Tambah anggota</x-button>
      @endcan
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">No. Anggota</th>
            <th class="{{ $th }}">Nama</th>
            <th class="{{ $th }}">Kontak</th>
            <th class="{{ $th }} text-center">Status</th>
            <th class="{{ $th }} text-center">Bergabung</th>
            <th class="{{ $th }} text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($members as $member)
            <tr>
              <td class="{{ $td }} text-sm font-semibold dark:text-white">{{ $member->member_number }}</td>
              <td class="{{ $td }} text-sm dark:text-white">{{ $member->name }} @if ($member->user)<i class="ml-1 text-xs text-emerald-500 fas fa-user-check" title="Punya akun login"></i>@endif</td>
              <td class="{{ $td }} text-xs text-slate-400">{{ $member->email ?: '—' }}</td>
              <td class="{{ $td }} text-center">
                <span class="bg-gradient-to-tl {{ $member->status->badgeColor() }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $member->status->label() }}</span>
              </td>
              <td class="{{ $td }} text-center text-xs text-slate-400">{{ $member->joined_at->format('d/m/Y') }}</td>
              <td class="{{ $td }} text-center">
                @can('update', $member)
                  <a href="{{ route('members.edit', $member) }}" class="mr-3 text-xs font-semibold text-slate-400 hover:text-blue-500"><i class="mr-1 fas fa-pen"></i>Ubah</a>
                @endcan
                @can('delete', $member)
                  <form method="POST" action="{{ route('members.destroy', $member) }}" class="inline" onsubmit="return confirm('Hapus anggota {{ addslashes($member->name) }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-0 text-xs font-semibold bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600"><i class="mr-1 fas fa-trash"></i>Hapus</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="p-6 text-sm text-center dark:text-white/80">Belum ada anggota.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($members->hasPages())
      <div class="px-6 pt-4">{{ $members->withQueryString()->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
