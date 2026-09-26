@props(['for' => null, 'required' => false])

<label @if ($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80']) }}>
  {{ $slot }}
  @if ($required)
    <span class="text-red-500">*</span>
  @endif
</label>
