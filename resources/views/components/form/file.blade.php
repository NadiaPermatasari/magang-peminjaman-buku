{{--
  Single file input with name display and image preview.
  <x-form.file name="avatar" label="Photo" accept="image/*" :preview="$user->avatar_url" help="JPG/PNG up to 2 MB" />
--}}
@props([
  'name',
  'label' => null,
  'help' => null,
  'required' => false,
  'accept' => null,
  'preview' => null,
  'bag' => 'default',
  'id' => null,
  'buttonText' => 'Choose file',
])

@php
  $id = $id ?? $name;
  $hasError = $errors->{$bag}->has($name);
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}" data-file-input>
  @if ($label)
    <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
  @endif

  <div class="flex items-center p-2 border border-solid rounded-lg {{ $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20' }} bg-white dark:bg-slate-850">
    <img data-file-preview data-placeholder="{{ $preview }}" src="{{ $preview }}" alt="" class="object-cover w-12 h-12 mr-3 rounded-lg {{ $preview ? '' : 'hidden' }}" />
    <label for="{{ $id }}" class="inline-block px-4 py-2 mb-0 mr-3 text-xs font-bold leading-normal text-center text-white align-middle transition-all ease-in bg-slate-700 border-0 rounded-lg shadow-md cursor-pointer tracking-tight-rem hover:shadow-xs hover:-translate-y-px active:opacity-85 whitespace-nowrap">
      <i class="mr-1 fas fa-upload"></i> {{ $buttonText }}
    </label>
    <span data-file-name data-placeholder="No file chosen" class="flex-1 min-w-0 text-sm truncate text-slate-500 dark:text-white/70">No file chosen</span>
    <button type="button" data-file-clear class="hidden p-0 ml-2 text-sm bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600" aria-label="Clear"><i class="fas fa-times"></i></button>
    <input type="file" id="{{ $id }}" name="{{ $name }}" class="sr-only" @if ($accept) accept="{{ $accept }}" @endif @if ($required) required @endif {{ $attributes->except('class') }} />
  </div>

  @if ($help)
    <p class="mt-1 ml-1 text-xs text-slate-400">{{ $help }}</p>
  @endif
  <x-form.error :name="$name" :bag="$bag" />
</div>
