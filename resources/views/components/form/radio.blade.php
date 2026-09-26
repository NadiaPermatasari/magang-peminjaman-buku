{{-- <x-form.radio name="plan" value="pro" label="Pro" :checked="old('plan', 'free') === 'pro'" /> --}}
@props([
  'name',
  'value',
  'label' => null,
  'checked' => false,
  'help' => null,
  'id' => null,
])

@php
  $id = $id ?? $name.'_'.\Illuminate\Support\Str::slug((string) $value);
  $old = old($name);
  $isChecked = $old === null ? $checked : (string) $old === (string) $value;
@endphp

<div class="{{ $attributes->get('class', 'min-h-6 pl-7 mb-2 block') }}">
  <input
    type="radio"
    id="{{ $id }}"
    name="{{ $name }}"
    value="{{ $value }}"
    @checked($isChecked)
    {{ $attributes->except('class') }}
    class="w-4.8 h-4.8 ease -ml-7 rounded-full checked:bg-gradient-to-tl checked:from-blue-500 checked:to-violet-500 after:rounded-full after:duration-250 after:ease-in-out duration-250 relative float-left mt-1 cursor-pointer appearance-none border border-solid border-slate-200 dark:border-white/30 bg-white dark:bg-slate-850 align-top transition-all after:absolute after:left-1/2 after:top-1/2 after:h-2 after:w-2 after:-translate-x-1/2 after:-translate-y-1/2 after:bg-white after:opacity-0 after:transition-all after:content-[''] checked:border-0 checked:border-transparent checked:after:opacity-100 disabled:opacity-50 disabled:cursor-not-allowed"
  />
  @if ($label)
    <label for="{{ $id }}" class="mb-0 ml-1 font-normal cursor-pointer select-none text-sm text-slate-700 dark:text-white/80">{{ $label }}</label>
  @endif
  @if ($help)
    <p class="mb-0 ml-1 text-xs text-slate-400">{{ $help }}</p>
  @endif
</div>
