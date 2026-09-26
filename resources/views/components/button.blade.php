{{--
  <x-button>Save</x-button>                      primary (blue)
  <x-button variant="dark|success|danger|warning|info|outline|light|link" size="sm|md|lg" type="submit" href="...">
  <x-button variant="danger" icon="fas fa-trash">Delete</x-button>
--}}
@props([
  'variant' => 'primary',
  'size' => 'md',
  'type' => 'button',
  'href' => null,
  'icon' => null,
  'block' => false,
])

@php
  $variants = [
    'primary' => 'text-white bg-blue-500 border-0 shadow-md hover:shadow-xs',
    'gradient' => 'text-white bg-gradient-to-tl from-blue-500 to-violet-500 border-0 shadow-md hover:shadow-xs',
    'dark' => 'text-white bg-slate-700 border-0 shadow-md hover:shadow-xs',
    'success' => 'text-white bg-gradient-to-tl from-emerald-500 to-teal-400 border-0 shadow-md hover:shadow-xs',
    'danger' => 'text-white bg-gradient-to-tl from-red-600 to-orange-600 border-0 shadow-md hover:shadow-xs',
    'warning' => 'text-white bg-gradient-to-tl from-orange-500 to-yellow-500 border-0 shadow-md hover:shadow-xs',
    'info' => 'text-white bg-gradient-to-tl from-blue-700 to-cyan-500 border-0 shadow-md hover:shadow-xs',
    'outline' => 'text-slate-700 dark:text-white bg-transparent border border-solid border-slate-700 dark:border-white shadow-none hover:bg-slate-700 hover:text-white',
    'outline-primary' => 'text-blue-500 bg-transparent border border-solid border-blue-500 shadow-none hover:bg-blue-500 hover:text-white',
    'light' => 'text-slate-700 bg-gray-100 border-0 shadow-md hover:shadow-xs dark:bg-slate-700 dark:text-white',
    'link' => 'text-blue-500 bg-transparent border-0 shadow-none p-0 hover:underline',
  ];
  $sizes = [
    'sm' => 'px-4 py-1.5 text-xs',
    'md' => 'px-6 py-2 text-xs',
    'lg' => 'px-8 py-3 text-sm',
  ];
  $classes = 'inline-flex items-center justify-center font-bold leading-normal text-center align-middle transition-all ease-in rounded-lg cursor-pointer tracking-tight-rem hover:-translate-y-px active:opacity-85 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0 '
    .($variants[$variant] ?? $variants['primary']).' '
    .($variant === 'link' ? '' : ($sizes[$size] ?? $sizes['md']))
    .($block ? ' w-full' : '');
@endphp

@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)<i class="{{ $icon }} mr-1"></i>@endif{{ $slot }}
  </a>
@else
  <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)<i class="{{ $icon }} mr-1"></i>@endif{{ $slot }}
  </button>
@endif
