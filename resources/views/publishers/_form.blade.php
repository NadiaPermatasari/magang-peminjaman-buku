@php $isEdit = $publisher->exists; @endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="name" label="Nama penerbit" :value="$publisher->name" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="slug" label="Slug" :value="$publisher->slug" help="Kosongkan untuk dibuat otomatis dari nama." />
  </div>
  <div class="w-full max-w-full px-3 md:w-8/12">
    <x-form.input name="address" label="Alamat" :value="$publisher->address" icon="fas fa-map-marker-alt" />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="phone" label="Telepon" :value="$publisher->phone" icon="fas fa-phone" />
  </div>
</div>
