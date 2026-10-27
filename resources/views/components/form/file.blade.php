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
  'buttonText' => 'Pilih file',
])

@php
  $id = $id ?? $name;
  $hasError = $errors->{$bag}->has($name);
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}" data-file-input>
  @if ($label)
    <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
  @endif

  <div class="flex flex-wrap items-center min-w-0 gap-2 p-2 border border-solid rounded-lg {{ $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20' }} bg-white dark:bg-slate-850">
    <img data-file-preview data-placeholder="{{ $preview }}" src="{{ $preview }}" alt="" class="object-cover w-12 h-12 rounded-lg shrink-0 {{ $preview ? '' : 'hidden' }}" />
    <label for="{{ $id }}" class="inline-flex items-center justify-center min-h-11 px-4 py-2 mb-0 text-xs font-bold leading-normal text-center text-white align-middle transition-all ease-in bg-slate-700 border-0 rounded-lg shadow-md cursor-pointer tracking-tight-rem hover:shadow-xs hover:-translate-y-px active:opacity-85 whitespace-nowrap shrink-0">
      <i class="mr-1 fas fa-upload"></i> {{ $buttonText }}
    </label>
    <span data-file-name data-placeholder="Belum ada file dipilih" class="order-last flex-1 min-w-0 text-sm truncate basis-full sm:order-none sm:basis-0 text-slate-500 dark:text-white/70">Belum ada file dipilih</span>
    <button type="button" data-file-clear class="hidden p-2 text-sm bg-transparent border-0 rounded-lg cursor-pointer shrink-0 text-slate-400 hover:text-red-600" aria-label="Hapus pilihan file"><i class="fas fa-times"></i></button>
    <input type="file" id="{{ $id }}" name="{{ $name }}" class="sr-only" @if ($accept) accept="{{ $accept }}" @endif @if ($required) required @endif {{ $attributes->except('class') }} />
  </div>

  @if ($help)
    <p class="mt-1 ml-1 text-xs text-slate-400">{{ $help }}</p>
  @endif
  <x-form.error :name="$name" :bag="$bag" />
</div>
