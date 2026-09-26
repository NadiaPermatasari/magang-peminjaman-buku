@php $isEdit = $rack->exists; @endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="code" label="Kode rak" :value="$rack->code" required help="Contoh: A1, RAK-01." />
  </div>
  <div class="w-full max-w-full px-3 md:w-8/12">
    <x-form.input name="name" label="Nama rak" :value="$rack->name" required />
  </div>
  <div class="w-full max-w-full px-3">
    <x-form.input name="location" label="Lokasi" :value="$rack->location" icon="fas fa-map-marker-alt" />
  </div>
</div>
