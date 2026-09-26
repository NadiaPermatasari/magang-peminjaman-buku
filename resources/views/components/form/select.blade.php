{{--
  <x-form.select name="country" label="Country" :options="['ID' => 'Indonesia', 'MY' => 'Malaysia']" :selected="$user->country" placeholder="Choose..." />

  Option groups: :options="['Backend' => ['php' => 'PHP'], 'Frontend' => ['vue' => 'Vue']]"
  select2-style (search):   searchable
  multiple:                 multiple  (name becomes name[] automatically, :selected="['a','b']")
  tags (create new):        searchable multiple taggable
--}}
@props([
  'name',
  'label' => null,
  'options' => [],
  'selected' => null,
  'placeholder' => null,
  'help' => null,
  'required' => false,
  'multiple' => false,
  'searchable' => false,
  'taggable' => false,
  'clearable' => false,
  'bag' => 'default',
  'id' => null,
])

@php
  $id = $id ?? $name;
  $key = rtrim($name, '[]');
  $hasError = $errors->{$bag}->has($key) || $errors->{$bag}->has($key.'.*');
  $fieldName = $multiple && ! str_ends_with($name, '[]') ? $name.'[]' : $name;
  $current = old($key, $selected);
  $current = $multiple ? array_map('strval', (array) ($current ?? [])) : (is_null($current) ? null : (string) $current);
  $useTom = $searchable || $multiple || $taggable;
  $base = 'focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all focus:border-blue-500 focus:outline-none';
  $border = $hasError ? 'border-red-500' : 'border-gray-300 dark:border-white/20';
  $isSelected = fn ($value) => $multiple ? in_array((string) $value, $current, true) : ((string) $value === $current);
  $grouped = collect($options)->contains(fn ($v) => is_array($v));
@endphp

<div class="{{ $attributes->get('class', 'mb-4') }}">
  @if ($label)
    <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
  @endif

  <select
    id="{{ $id }}"
    name="{{ $fieldName }}"
    @if ($multiple) multiple @endif
    @if ($required) required @endif
    @if ($useTom)
      data-tom-select
      data-placeholder="{{ $placeholder ?? 'Select...' }}"
      @if ($taggable) data-create="true" data-create-on-blur="true" @endif
      @if ($clearable) data-clear="true" @endif
    @endif
    {{ $attributes->except('class')->merge(['class' => "$base $border".($hasError ? ' is-invalid' : '')]) }}
  >
    @if (! $multiple)
      <option value="" @if ($current === null || $current === '') selected @endif @if ($required) disabled @endif>{{ $placeholder ?? 'Select...' }}</option>
    @endif

    @if ($grouped)
      @foreach ($options as $group => $items)
        <optgroup label="{{ $group }}">
          @foreach ($items as $value => $text)
            <option value="{{ $value }}" @selected($isSelected($value))>{{ $text }}</option>
          @endforeach
        </optgroup>
      @endforeach
    @else
      @foreach ($options as $value => $text)
        <option value="{{ $value }}" @selected($isSelected($value))>{{ $text }}</option>
      @endforeach
    @endif

    {{-- tags that were typed by the user but are not in $options (e.g. after a validation error) --}}
    @if ($taggable && $multiple)
      @foreach ($current as $value)
        @if (! array_key_exists($value, $options))
          <option value="{{ $value }}" selected>{{ $value }}</option>
        @endif
      @endforeach
    @endif
  </select>

  @if ($help)
    <p class="mt-1 ml-1 text-xs text-slate-400">{{ $help }}</p>
  @endif
  <x-form.error :name="$key" :bag="$bag" />
  <x-form.error :name="$key.'.*'" :bag="$bag" />
</div>
