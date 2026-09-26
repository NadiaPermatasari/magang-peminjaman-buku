{{--
  Drag & drop multi-file upload (previews, remove, limits) — submits as a normal file input.
  <x-form.dropzone name="attachments" label="Attachments" accept=".jpg,.png,.pdf" :max-files="5" :max-size="5" help="Up to 5 files, 5 MB each" />
  (max-size in MB). Validate with 'attachments' => ['array', 'max:5'], 'attachments.*' => ['file', 'max:5120'].
--}}
@props([
  'name',
  'label' => null,
  'help' => null,
  'accept' => null,
  'maxFiles' => 0,
  'maxSize' => 0,
  'bag' => 'default',
  'id' => null,
])

@php
  $id = $id ?? $name;
  $key = rtrim($name, '[]');
  $fieldName = str_ends_with($name, '[]') ? $name : $name.'[]';
  $hasError = $errors->{$bag}->has($key) || $errors->{$bag}->has($key.'.*');
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}">
  @if ($label)
    <x-form.label :for="$id">{{ $label }}</x-form.label>
  @endif

  <div data-dropzone data-max-files="{{ (int) $maxFiles }}" data-max-size="{{ (int) $maxSize * 1024 * 1024 }}" class="p-4 transition-all border-2 border-dashed rounded-xl {{ $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20' }} bg-gray-50 dark:bg-slate-850">
    <div class="py-6 text-center">
      <div class="inline-flex items-center justify-center w-12 h-12 mb-3 text-white rounded-circle bg-gradient-to-tl from-blue-500 to-violet-500">
        <i class="text-lg fas fa-cloud-upload-alt"></i>
      </div>
      <p class="mb-1 text-sm font-semibold text-slate-700 dark:text-white">Drag &amp; drop files here</p>
      <p class="mb-3 text-xs text-slate-400">or</p>
      <button type="button" data-dropzone-browse class="inline-block px-6 py-2 mb-0 text-xs font-bold leading-normal text-center text-white align-middle transition-all ease-in bg-blue-500 border-0 rounded-lg shadow-md cursor-pointer tracking-tight-rem hover:shadow-xs hover:-translate-y-px active:opacity-85">Browse files</button>
      <p data-dropzone-count data-placeholder="" class="mt-3 mb-0 text-xs font-semibold text-slate-500 dark:text-white/70"></p>
      <p data-dropzone-error class="hidden mt-2 mb-0 text-xs text-red-600"></p>
    </div>
    <div data-dropzone-list></div>
    <input type="file" id="{{ $id }}" name="{{ $fieldName }}" multiple class="sr-only" @if ($accept) accept="{{ $accept }}" @endif {{ $attributes->except('class') }} />
  </div>

  @if ($help)
    <p class="mt-1 ml-1 text-xs text-slate-400">{{ $help }}</p>
  @endif
  <x-form.error :name="$key" :bag="$bag" />
  <x-form.error :name="$key.'.*'" :bag="$bag" />
</div>
