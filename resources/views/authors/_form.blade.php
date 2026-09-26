@php $isEdit = $author->exists; @endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="name" label="Nama penulis" :value="$author->name" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="slug" label="Slug" :value="$author->slug" help="Kosongkan untuk dibuat otomatis dari nama." />
  </div>
  <div class="w-full max-w-full px-3">
    <x-form.textarea name="bio" label="Biografi" :value="$author->bio" rows="3" maxlength="2000" counter />
  </div>
</div>
