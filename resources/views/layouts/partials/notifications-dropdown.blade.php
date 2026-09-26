{{-- Navbar bell: latest notifications from the database + unread badge. --}}
@php
  $latest = auth()->user()->notifications()->latest()->take(5)->get();
  $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<li class="relative flex items-center pr-2">
  <p class="hidden transform-dropdown-show"></p>
  <a href="javascript:;" class="relative block p-0 text-sm text-white transition-all ease-nav-brand" dropdown-trigger aria-expanded="false" aria-label="Notifications">
    <i class="cursor-pointer fa fa-bell"></i>
    @if ($unreadCount > 0)
      <span class="absolute -top-2 -right-2 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white rounded-full bg-gradient-to-tl from-red-600 to-orange-600 pointer-events-none">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
    @endif
  </a>

  <ul dropdown-menu class="text-sm transform-dropdown before:font-awesome before:leading-default before:duration-350 before:ease lg:shadow-3xl duration-250 min-w-44 w-80 before:sm:right-8 before:text-5.5 pointer-events-none absolute right-0 top-0 z-50 origin-top list-none rounded-lg border-0 border-solid border-transparent dark:shadow-dark-xl dark:bg-slate-850 bg-white bg-clip-padding px-2 py-4 text-left text-slate-500 opacity-0 transition-all before:absolute before:right-2 before:left-auto before:top-0 before:z-50 before:inline-block before:font-normal before:text-white before:antialiased before:transition-all before:content-['\f0d8'] sm:-mr-6 lg:absolute lg:right-0 lg:left-auto lg:mt-2 lg:block lg:cursor-pointer">
    <li class="flex items-center justify-between px-4 pb-2 mb-2 border-b border-solid border-gray-200 dark:border-white/10">
      <span class="text-xs font-bold uppercase text-slate-700 dark:text-white">Notifications</span>
      @if ($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
          @csrf
          <button type="submit" class="p-0 text-xs font-semibold text-blue-500 bg-transparent border-0 cursor-pointer">Mark all read</button>
        </form>
      @endif
    </li>

    @forelse ($latest as $notification)
      @php $data = $notification->data; @endphp
      <li class="relative mb-2">
        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
          @csrf
          <button type="submit" class="dark:hover:bg-slate-900 ease py-1.2 clear-both block w-full whitespace-nowrap rounded-lg bg-transparent px-4 text-left duration-300 hover:bg-gray-200 hover:text-slate-700 lg:transition-colors border-0 cursor-pointer">
            <div class="flex py-1">
              <div class="inline-flex items-center justify-center my-auto mr-4 text-sm text-white transition-all duration-200 ease-nav-brand bg-gradient-to-tl {{ $data['color'] ?? 'from-blue-500 to-violet-500' }} h-9 w-9 rounded-xl shrink-0">
                <i class="{{ $data['icon'] ?? 'ni ni-bell-55' }}"></i>
              </div>
              <div class="flex flex-col justify-center min-w-0">
                <h6 class="mb-1 text-sm leading-normal truncate dark:text-white {{ $notification->read_at ? 'font-normal' : 'font-semibold' }}">{{ $data['title'] ?? 'Notification' }}</h6>
                <p class="mb-0 text-xs leading-tight truncate text-slate-400 dark:text-white/80">
                  <i class="mr-1 fa fa-clock"></i>
                  {{ $notification->created_at->diffForHumans() }}
                </p>
              </div>
              @unless ($notification->read_at)
                <span class="w-2 h-2 my-auto ml-auto rounded-full bg-blue-500 shrink-0"></span>
              @endunless
            </div>
          </button>
        </form>
      </li>
    @empty
      <li class="px-4 py-3 text-xs text-center text-slate-400">No notifications yet.</li>
    @endforelse

    <li class="pt-2 mt-1 text-center border-t border-solid border-gray-200 dark:border-white/10">
      <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-blue-500">View all notifications</a>
    </li>
  </ul>
</li>
