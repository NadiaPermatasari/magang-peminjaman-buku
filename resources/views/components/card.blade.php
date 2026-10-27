{{--
  Argon card.
  <x-card title="Users" subtitle="16 registered">
    <x-slot:actions><x-button>Add</x-button></x-slot:actions>
    ...body...
  </x-card>
  Use padding="false" for flush content (tables).
--}}
@props([
  'title' => null,
  'subtitle' => null,
  'padding' => true,
])

<div {{ $attributes->merge(['class' => 'relative flex flex-col min-w-0 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border']) }}>
  @if ($title || isset($actions))
    <div class="flex flex-wrap items-center gap-3 p-4 pb-0 mb-0 border-b-0 rounded-t-2xl sm:p-6 sm:pb-0">
      <div class="min-w-0 max-w-full mr-auto">
        @if ($title)
          <h6 class="mb-0 dark:text-white">{{ $title }}</h6>
        @endif
        @if ($subtitle)
          <p class="mb-0 text-sm leading-normal dark:text-white dark:opacity-60">{{ $subtitle }}</p>
        @endif
      </div>
      @isset($actions)
        <div class="card-actions flex flex-wrap items-center w-full min-w-0 max-w-full gap-2 sm:w-auto">{{ $actions }}</div>
      @endisset
    </div>
  @endif
  <div class="flex-auto min-w-0 {{ $padding ? 'p-4 sm:p-6' : 'px-0 pt-0 pb-2' }}">
    {{ $slot }}
  </div>
  @isset($footer)
    <div class="p-4 pt-0 sm:p-6 sm:pt-0">{{ $footer }}</div>
  @endisset
</div>
