@php $isEdit = $category->exists; @endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="name" label="Nama kategori" :value="$category->name" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="slug" label="Slug" :value="$category->slug" help="Kosongkan untuk dibuat otomatis dari nama." />
  </div>
  <div class="w-full max-w-full px-3">
    <x-form.textarea name="description" label="Deskripsi" :value="$category->description" rows="3" maxlength="500" counter />
  </div>
</div>
