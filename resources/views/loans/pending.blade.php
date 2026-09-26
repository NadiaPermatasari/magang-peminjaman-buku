@extends('layouts.app')

@section('title', 'Menunggu Verifikasi')
@section('page', 'loans')

@section('content')
  <x-card title="Menunggu Verifikasi" :subtitle="$loans->total().' pengajuan'" :padding="false">
    <div class="p-6">
      @forelse ($loans as $loan)
        <div class="p-4 mb-4 border border-solid rounded-xl border-gray-200 dark:border-white/10 last:mb-0">
          <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
            <div>
              <a href="{{ route('loans.show', $loan) }}" class="text-sm font-semibold dark:text-white hover:text-blue-500">{{ $loan->code }}</a>
              <p class="mb-0 text-xs text-slate-400">{{ $loan->member->name }} ({{ $loan->member->member_number }}) — diajukan {{ $loan->requested_at->diffForHumans() }}</p>
              <p class="mb-0 text-xs text-slate-400">{{ $loan->items->pluck('book.title')->join(', ') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <form method="POST" action="{{ route('loans.approve', $loan) }}" onsubmit="return confirm('Setujui peminjaman ini? Tindakan ini akan mereservasi eksemplar buku.');">
                @csrf
                <x-button type="submit" variant="success" size="sm" icon="fas fa-check">Setujui</x-button>
              </form>
              <button type="button" onclick="document.getElementById('reject-{{ $loan->id }}').classList.toggle('hidden')" class="inline-flex items-center justify-center px-4 py-1.5 text-xs font-bold text-white transition-all bg-gradient-to-tl from-red-600 to-orange-600 rounded-lg shadow-md cursor-pointer hover:-translate-y-px">
                <i class="mr-1 fas fa-ban"></i>Tolak
              </button>
            </div>
          </div>
          <form id="reject-{{ $loan->id }}" method="POST" action="{{ route('loans.reject', $loan) }}" class="hidden pt-3 mt-3 border-t border-solid border-gray-200 dark:border-white/10">
            @csrf
            <div class="flex flex-wrap items-end gap-2">
              <div class="flex-1 min-w-48">
                <x-form.input name="reason" label="Alasan penolakan (wajib)" required class="mb-0" />
              </div>
              <x-button type="submit" variant="danger" size="sm">Kirim Penolakan</x-button>
            </div>
          </form>
        </div>
      @empty
        <p class="py-6 text-sm text-center text-slate-400">Tidak ada pengajuan yang menunggu verifikasi.</p>
      @endforelse
    </div>

    @if ($loans->hasPages())
      <div class="px-6 pb-4">{{ $loans->links('pagination.argon') }}</div>
    @endif
  </x-card>
@endsection
