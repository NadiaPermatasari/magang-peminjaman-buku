{{-- <x-form.textarea name="bio" label="About" rows="4" :value="$user->about_me" maxlength="500" counter /> --}}
@props([
  'name',
  'label' => null,
  'value' => null,
  'placeholder' => null,
  'help' => null,
  'required' => false,
  'rows' => 3,
  'bag' => 'default',
  'id' => null,
  'counter' => false,
])

@php
  $id = $id ?? $name;
  $hasError = $errors->{$bag}->has($name);
  $base = 'focus:shadow-primary-outline dark:bg-slate-850 dark:text-white dark:placeholder:text-white/60 text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none disabled:bg-gray-100';
  $border = $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20';
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}">
  @if ($label)
    <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
  @endif
  <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif @if ($required) required @endif {{ $attributes->except('class')->merge(['class' => "$base $border"]) }}>{{ old($name, $value) }}</textarea>
  <div class="flex justify-between">
    @if ($help)
      <p class="mt-1 ml-1 text-xs text-slate-400">{{ $help }}</p>
    @else
      <span></span>
    @endif
    @if ($counter)
      <span class="mt-1 mr-1 text-xs text-slate-400" data-count-for="{{ $id }}"></span>
    @endif
  </div>
  <x-form.error :name="$name" :bag="$bag" />
</div>
