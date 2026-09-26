@props(['name', 'bag' => 'default'])

@error($name, $bag)
  <p {{ $attributes->merge(['class' => 'mt-1 ml-1 text-xs text-red-600']) }}>{{ $message }}</p>
@enderror
