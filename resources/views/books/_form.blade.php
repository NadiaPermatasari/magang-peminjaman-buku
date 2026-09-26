@php
  $isEdit = $book->exists;
  $categoryOptions = $categories->pluck('name', 'id')->all();
  $publisherOptions = $publishers->pluck('name', 'id')->all();
  $rackOptions = $racks->mapWithKeys(fn ($r) => [$r->id => "{$r->code} — {$r->name}"])->all();
  $authorOptions = $authors->pluck('name', 'id')->all();
  $selectedAuthors = $isEdit ? $book->authors->pluck('id')->all() : [];
@endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-8/12">
    <x-form.input name="title" label="Judul buku" :value="$book->title" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="isbn" label="ISBN" :value="$book->isbn" />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="slug" label="Slug" :value="$book->slug" help="Kosongkan untuk dibuat otomatis dari judul." />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.select name="authors" label="Penulis" :options="$authorOptions" :selected="$selectedAuthors" multiple searchable placeholder="Pilih penulis..." />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.select name="category_id" label="Kategori" :options="$categoryOptions" :selected="$book->category_id" required searchable />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.select name="publisher_id" label="Penerbit" :options="$publisherOptions" :selected="$book->publisher_id" required searchable />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.select name="rack_id" label="Rak" :options="$rackOptions" :selected="$book->rack_id" searchable placeholder="Tanpa rak" />
  </div>
  <div class="w-full max-w-full px-3 md:w-3/12">
    <x-form.input name="publication_year" label="Tahun terbit" type="number" :value="$book->publication_year" />
  </div>
  <div class="w-full max-w-full px-3 md:w-3/12">
    <x-form.input name="edition" label="Edisi" :value="$book->edition" />
  </div>
  <div class="w-full max-w-full px-3 md:w-3/12">
    <x-form.input name="language" label="Bahasa" :value="$book->language" />
  </div>
  <div class="w-full max-w-full px-3 md:w-3/12">
    <x-form.input name="page_count" label="Jumlah halaman" type="number" :value="$book->page_count" />
  </div>
  <div class="w-full max-w-full px-3">
    <x-form.textarea name="description" label="Deskripsi" :value="$book->description" rows="4" maxlength="5000" counter />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.file name="cover" label="Sampul buku" accept="image/png,image/jpeg,image/webp" :preview="$book->cover_url" help="JPG/PNG/WEBP, maks 2 MB." />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.toggle name="is_active" label="Buku aktif (tampil di katalog)" :checked="$isEdit ? (bool) $book->is_active : true" />
  </div>
</div>
