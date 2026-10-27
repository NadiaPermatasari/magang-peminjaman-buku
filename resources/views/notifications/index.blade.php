@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page', 'notifications')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 mx-auto lg:w-9/12 lg:flex-none">
      <x-card title="Notifikasi" :subtitle="$unreadCount.' belum dibaca'" :padding="false">
        <x-slot:actions>
          <a href="{{ route('notifications.index') }}" class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-lg {{ request('filter') !== 'unread' ? 'text-white bg-blue-500' : 'text-slate-500 bg-gray-100 dark:bg-slate-700 dark:text-white' }}">Semua</a>
          <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-lg {{ request('filter') === 'unread' ? 'text-white bg-blue-500' : 'text-slate-500 bg-gray-100 dark:bg-slate-700 dark:text-white' }}">Belum dibaca</a>
          @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
              @csrf
              <x-button type="submit" variant="dark" size="sm" icon="fas fa-check-double">Tandai semua dibaca</x-button>
            </form>
          @endif
          @if ($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.destroy-all') }}" onsubmit="return confirm('Hapus semua notifikasi?');">
              @csrf
              @method('DELETE')
              <x-button type="submit" variant="outline" size="sm" icon="fas fa-trash">Hapus semua</x-button>
            </form>
          @endif
        </x-slot:actions>

        <ul class="flex flex-col pl-0 mb-0">
          @forelse ($notifications as $notification)
            @php $data = $notification->data; @endphp
            <li class="relative flex flex-wrap items-start gap-3 px-4 py-4 border-b border-solid border-gray-200 sm:px-6 dark:border-white/10 {{ $notification->read_at ? '' : 'bg-blue-500/5' }}">
              <div class="inline-flex items-center justify-center w-10 h-10 text-white rounded-xl bg-gradient-to-tl {{ $data['color'] ?? 'from-blue-500 to-violet-500' }} shrink-0">
                <i class="{{ $data['icon'] ?? 'ni ni-bell-55' }}"></i>
              </div>
              <div class="flex-1 min-w-0">
                <h6 class="mb-1 text-sm leading-normal dark:text-white {{ $notification->read_at ? 'font-normal' : 'font-semibold' }}">
                  {{ $data['title'] ?? 'Notifikasi' }}
                  @unless ($notification->read_at)
                    <span class="ml-2 px-2 py-0.5 text-xxs font-bold text-white uppercase rounded-md bg-gradient-to-tl from-blue-500 to-violet-500">Baru</span>
                  @endunless
                </h6>
                @if (! empty($data['message']))
                  <p class="mb-1 text-sm leading-normal text-slate-500 dark:text-white/70">{{ $data['message'] }}</p>
                @endif
                <p class="mb-0 text-xs text-slate-400"><i class="mr-1 fa fa-clock"></i>{{ $notification->created_at->diffForHumans() }} · {{ $notification->created_at->format('d M Y, H:i') }}</p>
              </div>
              <div class="flex items-center justify-end w-full gap-2 shrink-0 sm:w-auto sm:ml-1">
                @if (! empty($data['url']) || ! $notification->read_at)
                  <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center w-11 h-11 text-xs font-semibold text-blue-500 bg-transparent border-0 rounded-lg cursor-pointer" aria-label="{{ ! empty($data['url']) ? 'Buka notifikasi' : 'Tandai dibaca' }}" title="{{ ! empty($data['url']) ? 'Buka notifikasi' : 'Tandai dibaca' }}">
                      <i class="fas {{ ! empty($data['url']) ? 'fa-arrow-right' : 'fa-check' }}"></i>
                    </button>
                  </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="inline-flex items-center justify-center w-11 h-11 text-xs bg-transparent border-0 rounded-lg cursor-pointer text-slate-400 hover:text-red-600" aria-label="Hapus notifikasi" title="Hapus notifikasi"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </li>
          @empty
            <li class="px-6 py-12 text-center">
              <div class="inline-flex items-center justify-center w-16 h-16 mb-3 text-white rounded-circle bg-gradient-to-tl from-slate-600 to-slate-300"><i class="text-xl ni ni-bell-55"></i></div>
              <p class="mb-0 text-sm text-slate-500 dark:text-white/70">{{ request('filter') === 'unread' ? 'Semua notifikasi sudah dibaca.' : 'Belum ada notifikasi.' }}</p>
            </li>
          @endforelse
        </ul>

        @if ($notifications->hasPages())
          <div class="px-6 pt-4">
            {{ $notifications->withQueryString()->links('pagination.argon') }}
          </div>
        @endif
      </x-card>
    </div>
  </div>
@endsection
