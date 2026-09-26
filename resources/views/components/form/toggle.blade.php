{{-- Argon switch: <x-form.toggle name="two_factor" label="Enable two-factor" :checked="true" /> (always submits 0/1) --}}
@props([
  'name',
  'label' => null,
  'checked' => false,
  'help' => null,
  'id' => null,
  'value' => '1',
])

@php
  $id = $id ?? $name;
  $old = old($name);
  $isChecked = $old === null ? $checked : (string) $old === (string) $value;
@endphp

<div class="{{ $attributes->get('class', 'flex items-start pl-12 mb-3 min-h-6') }}">
  <input type="hidden" name="{{ $name }}" value="0" />
  <input
    type="checkbox"
    id="{{ $id }}"
    name="{{ $name }}"
    value="{{ $value }}"
    @checked($isChecked)
    {{ $attributes->except('class') }}
    class="mt-0.5 rounded-10 duration-250 ease-in-out after:rounded-circle after:shadow-2xl after:duration-250 checked:after:translate-x-5.3 h-5 relative float-left -ml-12 w-10 cursor-pointer appearance-none border border-solid border-gray-200 dark:border-white/30 bg-zinc-700/10 bg-none bg-contain bg-left bg-no-repeat align-top transition-all after:absolute after:top-px after:h-4 after:w-4 after:translate-x-px after:bg-white after:content-[''] checked:border-blue-500/95 checked:bg-blue-500/95 checked:bg-none checked:bg-right disabled:opacity-50 disabled:cursor-not-allowed"
  />
  <div>
    @if ($label)
      <label for="{{ $id }}" class="mb-0 ml-2 font-normal cursor-pointer select-none text-sm text-slate-700 dark:text-white/80">{{ $label }}</label>
    @endif
    @if ($help)
      <p class="mb-0 ml-2 text-xs text-slate-400">{{ $help }}</p>
    @endif
  </div>
</div>
