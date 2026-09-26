@extends('layouts.app')

@section('title', 'Statistik')
@section('page', 'reports')

@section('content')
  <div class="flex flex-wrap -mx-3 mb-6">
    <div class="w-full max-w-full px-3">
      <x-card title="Tren Peminjaman (6 Bulan Terakhir)">
        <canvas id="chart-line" height="90"></canvas>
      </x-card>
    </div>
  </div>

  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mb-6 lg:w-4/12 lg:flex-none">
      <x-card title="Statistik Kategori">
        <ul class="pl-0 mb-0">
          @forelse ($categoryStats as $row)
            <li class="flex items-center justify-between py-1.5 border-b border-solid last:border-0 border-gray-100 dark:border-white/5">
              <span class="text-sm dark:text-white">{{ $row->name }}</span>
              <span class="text-xs font-semibold text-slate-400">{{ $row->total }}</span>
            </li>
          @empty
            <li class="py-2 text-sm text-slate-400">Belum ada data.</li>
          @endforelse
        </ul>
      </x-card>
    </div>

    <div class="w-full max-w-full px-3 mb-6 lg:w-4/12 lg:flex-none">
      <x-card title="Kondisi Eksemplar">
        <ul class="pl-0 mb-0">
          @forelse ($conditionStats as $row)
            <li class="flex items-center justify-between py-1.5 border-b border-solid last:border-0 border-gray-100 dark:border-white/5">
              <span class="text-sm dark:text-white">{{ $row->condition->label() }}</span>
              <span class="text-xs font-semibold text-slate-400">{{ $row->total }}</span>
            </li>
          @empty
            <li class="py-2 text-sm text-slate-400">Belum ada data.</li>
          @endforelse
        </ul>
      </x-card>
    </div>

    <div class="w-full max-w-full px-3 mb-6 lg:w-4/12 lg:flex-none">
      <x-card title="Anggota Paling Aktif">
        <ul class="pl-0 mb-0">
          @forelse ($activeMembers as $member)
            <li class="flex items-center justify-between py-1.5 border-b border-solid last:border-0 border-gray-100 dark:border-white/5">
              <span class="text-sm dark:text-white">{{ $member->name }}</span>
              <span class="text-xs font-semibold text-slate-400">{{ $member->loans_count }} pinjaman</span>
            </li>
          @empty
            <li class="py-2 text-sm text-slate-400">Belum ada data.</li>
          @endforelse
        </ul>
      </x-card>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    window.ARGON_CHARTS = {
      line: @json(['labels' => $chart['labels'], 'data' => $chart['data'], 'label' => 'Peminjaman']),
    };
  </script>
@endpush
