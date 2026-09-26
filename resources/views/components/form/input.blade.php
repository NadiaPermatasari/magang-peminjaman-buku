{{--
  <x-form.input name="email" label="Email" type="email" :value="$user->email" required
                placeholder="you@example.com" help="We never share it." icon="fas fa-envelope" addon="@" />

  Props: name, label, type, value, placeholder, help, required, icon (left icon),
         addon (left text addon), suffix (right text), bag (error bag), disabled, readonly,
         password-toggle (adds an eye button for type=password)
--}}
@props([
  'name',
  'label' => null,
  'type' => 'text',
  'value' => null,
  'placeholder' => null,
  'help' => null,
  'required' => false,
  'icon' => null,
  'addon' => null,
  'suffix' => null,
  'bag' => 'default',
  'id' => null,
  'passwordToggle' => false,
])

@php
  $id = $id ?? $name;
  $hasError = $errors->{$bag}->has($name);
  $base = 'focus:shadow-primary-outline dark:bg-slate-850 dark:text-white dark:placeholder:text-white/60 text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed read-only:bg-gray-50 dark:disabled:bg-slate-900';
  $border = $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20';
  $rounded = ($icon || $addon) ? 'rounded-l-none' : '';
  $rounded .= ($suffix || $passwordToggle) ? ' rounded-r-none' : '';
  $current = $type === 'password' ? null : old($name, $value);
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}">
  @if ($label)
    <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
  @endif

  <div class="flex flex-wrap items-stretch w-full">
    @if ($icon || $addon)
      <span class="flex items-center px-3 text-sm font-normal text-center border border-r-0 border-solid rounded-l-lg {{ $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20' }} bg-gray-50 text-slate-500 dark:bg-slate-900 dark:text-white/70">
        @if ($icon)<i class="{{ $icon }}"></i>@else{{ $addon }}@endif
      </span>
    @endif

    <input
      type="{{ $type }}"
      id="{{ $id }}"
      name="{{ $name }}"
      @if ($current !== null) value="{{ $current }}" @endif
      @if ($placeholder) placeholder="{{ $placeholder }}" @endif
      @if ($required) required @endif
      {{ $attributes->except('class')->merge(['class' => "$base $border $rounded flex-1 min-w-0"]) }}
    />

    @if ($passwordToggle)
      <button type="button" data-password-toggle="{{ $id }}" class="flex items-center px-3 text-sm bg-gray-50 border border-l-0 border-solid rounded-r-lg cursor-pointer {{ $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20' }} text-slate-500 dark:bg-slate-900 dark:text-white/70" aria-label="Show password">
        <i class="fas fa-eye"></i>
      </button>
    @elseif ($suffix)
      <span class="flex items-center px-3 text-sm font-normal text-center border border-l-0 border-solid rounded-r-lg {{ $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20' }} bg-gray-50 text-slate-500 dark:bg-slate-900 dark:text-white/70">{{ $suffix }}</span>
    @endif
  </div>

  @if ($help)
    <p class="mt-1 ml-1 text-xs text-slate-400">{{ $help }}</p>
  @endif
  <x-form.error :name="$name" :bag="$bag" />
</div>
