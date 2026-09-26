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
    <div class="flex flex-wrap items-center gap-2 p-6 pb-0 mb-0 border-b-0 rounded-t-2xl">
      <div class="mr-auto">
        @if ($title)
          <h6 class="mb-0 dark:text-white">{{ $title }}</h6>
        @endif
        @if ($subtitle)
          <p class="mb-0 text-sm leading-normal dark:text-white dark:opacity-60">{{ $subtitle }}</p>
        @endif
      </div>
      @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
      @endisset
    </div>
  @endif
  <div class="flex-auto {{ $padding ? 'p-6' : 'px-0 pt-0 pb-2' }}">
    {{ $slot }}
  </div>
  @isset($footer)
    <div class="p-6 pt-0">{{ $footer }}</div>
  @endisset
</div>
