{{--
  <x-form.checkbox name="newsletter" label="Subscribe to newsletter" :checked="$user->newsletter" />
  Group:  <x-form.checkbox name="interests[]" value="design" label="Design" :checked="in_array('design', old('interests', []))" />
  Sends "0" when unchecked only if hidden="true" (useful for boolean fields).
--}}
@props([
  'name',
  'label' => null,
  'value' => '1',
  'checked' => false,
  'help' => null,
  'bag' => 'default',
  'id' => null,
  'hidden' => false,
])

@php
  $key = rtrim($name, '[]');
  $id = $id ?? $key.'_'.\Illuminate\Support\Str::slug((string) $value);
  $old = old($key);
  $isChecked = $old === null ? $checked : (is_array($old) ? in_array((string) $value, array_map('strval', $old), true) : (string) $old === (string) $value);
@endphp

<div class="{{ $attributes->get('class', 'min-h-6 pl-7 mb-2 block') }}">
  @if ($hidden)
    <input type="hidden" name="{{ $name }}" value="0" />
  @endif
  <input
    type="checkbox"
    id="{{ $id }}"
    name="{{ $name }}"
    value="{{ $value }}"
    @checked($isChecked)
    {{ $attributes->except('class') }}
    class="w-4.8 h-4.8 ease -ml-7 rounded-1.4 checked:bg-gradient-to-tl checked:from-blue-500 checked:to-violet-500 after:text-xxs after:font-awesome after:duration-250 after:ease-in-out duration-250 relative float-left mt-1 cursor-pointer appearance-none border border-solid border-slate-200 dark:border-white/30 bg-white dark:bg-slate-850 bg-contain bg-center bg-no-repeat align-top transition-all after:absolute after:flex after:h-full after:w-full after:items-center after:justify-center after:text-white after:opacity-0 after:transition-all after:content-['\f00c'] checked:border-0 checked:border-transparent checked:bg-transparent checked:after:opacity-100 disabled:opacity-50 disabled:cursor-not-allowed"
  />
  @if ($label)
    <label for="{{ $id }}" class="mb-0 ml-1 font-normal cursor-pointer select-none text-sm text-slate-700 dark:text-white/80">{!! $label !!}</label>
  @endif
  @if ($help)
    <p class="mb-0 ml-1 text-xs text-slate-400">{{ $help }}</p>
  @endif
  <x-form.error :name="$key" :bag="$bag" class="mt-0" />
</div>
