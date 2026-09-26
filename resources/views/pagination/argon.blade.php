{{-- Argon-styled pagination view: {{ $items->links('pagination.argon') }} --}}
@if ($paginator->hasPages())
  @php
    $base = 'mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-solid border-gray-200 bg-transparent text-sm text-slate-500 transition-all duration-300 dark:border-white/40 dark:text-white';
    $activeCls = 'mx-1 flex h-9 w-9 items-center justify-center rounded-full border-0 bg-gradient-to-tl from-blue-500 to-violet-500 text-sm text-white shadow-md';
    $disabledCls = 'mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-solid border-gray-200 bg-transparent text-sm text-slate-300 dark:border-white/20 dark:text-white/40 cursor-not-allowed';
  @endphp
  <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between">
    <p class="mb-0 text-sm leading-normal dark:text-white/80">
      Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </p>
    <ul class="flex flex-wrap items-center pl-0 mb-0 list-none">
      {{-- Previous --}}
      @if ($paginator->onFirstPage())
        <li><span class="{{ $disabledCls }}" aria-disabled="true"><i class="fas fa-angle-left"></i></span></li>
      @else
        <li><a class="{{ $base }} hover:bg-gray-200 dark:hover:bg-slate-800" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="fas fa-angle-left"></i></a></li>
      @endif

      @foreach ($elements as $element)
        @if (is_string($element))
          <li><span class="{{ $disabledCls }}">{{ $element }}</span></li>
        @endif
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li><span class="{{ $activeCls }}" aria-current="page">{{ $page }}</span></li>
            @else
              <li><a class="{{ $base }} hover:bg-gray-200 dark:hover:bg-slate-800" href="{{ $url }}">{{ $page }}</a></li>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Next --}}
      @if ($paginator->hasMorePages())
        <li><a class="{{ $base }} hover:bg-gray-200 dark:hover:bg-slate-800" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="fas fa-angle-right"></i></a></li>
      @else
        <li><span class="{{ $disabledCls }}" aria-disabled="true"><i class="fas fa-angle-right"></i></span></li>
      @endif
    </ul>
  </nav>
@endif
