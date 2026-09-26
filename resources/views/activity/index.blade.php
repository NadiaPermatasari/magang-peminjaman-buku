@extends('layouts.app')

@section('title', 'Log Audit')
@section('page', 'activity')

@php
  $th = 'px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70';
  $td = 'p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent';
@endphp

@section('content')
  <x-card title="Log Audit" :subtitle="$activities->total().' entri'" :padding="false">
    <x-slot:actions>
      <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-center gap-2">
        <select name="action" data-tom-select data-search="false" data-placeholder="Semua aksi" class="text-sm rounded-lg border-gray-300 min-w-40">
          <option value="">Semua aksi</option>
          @foreach ($actions as $action)
            <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst(str_replace('_', ' ', strtolower($action))) }}</option>
          @endforeach
        </select>
        <select name="user" data-tom-select data-placeholder="Semua pengguna" class="text-sm rounded-lg border-gray-300 min-w-44">
          <option value="">Semua pengguna</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected((string) request('user') === (string) $user->id)>{{ $user->name }}</option>
          @endforeach
        </select>
        <x-button type="submit" variant="dark" size="sm" icon="fas fa-filter">Filter</x-button>
        @if (request()->hasAny(['action', 'user']))
          <x-button variant="link" size="sm" href="{{ route('audit-logs.index') }}">Reset</x-button>
        @endif
      </form>
    </x-slot:actions>

    <div class="p-0 overflow-x-auto">
      <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
        <thead class="align-bottom">
          <tr>
            <th class="{{ $th }}">Aktivitas</th>
            <th class="{{ $th }} pl-2">Pengguna</th>
            <th class="{{ $th }} text-center">Aksi</th>
            <th class="{{ $th }} text-center">Alamat IP</th>
            <th class="{{ $th }} text-center">Waktu</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($activities as $activity)
            <tr>
              <td class="{{ $td }}">
                <div class="flex items-center px-2 py-1">
                  <div class="inline-flex items-center justify-center w-9 h-9 mr-4 text-white rounded-xl bg-gradient-to-tl {{ $activity->color }} shrink-0">
                    <i class="{{ $activity->icon }} text-xxs"></i>
                  </div>
                  <div class="flex flex-col justify-center">
                    <h6 class="mb-0 text-sm leading-normal dark:text-white">{{ $activity->description }}</h6>
                    @if ($activity->subject_type && $activity->subject_id)
                      <p class="mb-0 text-xs leading-tight text-slate-400 dark:text-white/80">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</p>
                    @endif
                  </div>
                </div>
              </td>
              <td class="{{ $td }}">
                @if ($activity->user)
                  <div class="flex items-center">
                    <img src="{{ $activity->user->avatar_url }}" class="w-6 h-6 mr-2 rounded-lg" alt="" />
                    <div>
                      <p class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-80">{{ $activity->user->name }}</p>
                      <p class="mb-0 text-xs leading-tight text-slate-400 dark:text-white/80">{{ $activity->user->email }}</p>
                    </div>
                  </div>
                @else
                  <span class="text-xs text-slate-400">Sistem</span>
                @endif
              </td>
              <td class="{{ $td }} text-center">
                <span class="bg-gradient-to-tl {{ $activity->color }} px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">{{ $activity->action_label }}</span>
              </td>
              <td class="{{ $td }} text-center">
                <span class="text-xs font-semibold leading-tight dark:text-white dark:opacity-80 text-slate-400">{{ $activity->ip_address ?? '—' }}</span>
              </td>
              <td class="{{ $td }} text-center">
                <span class="text-xs font-semibold leading-tight dark:text-white dark:opacity-80 text-slate-400" title="{{ $activity->created_at->format('d M Y H:i:s') }}">{{ $activity->created_at->diffForHumans() }}</span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="p-6 text-sm text-center dark:text-white/80">Belum ada aktivitas tercatat.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($activities->hasPages())
      <div class="px-6 pt-4">
        {{ $activities->links('pagination.argon') }}
      </div>
    @endif
  </x-card>
@endsection
