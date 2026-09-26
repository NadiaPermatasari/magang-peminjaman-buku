@php
  $isEdit = $copy->exists;
  $bookOptions = $books->pluck('title', 'id')->all();
  $conditionOptions = collect($conditions)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
  $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
@endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.select name="book_id" label="Judul buku" :options="$bookOptions" :selected="$copy->book_id" required searchable />
  </div>
  <div class="w-full max-w-full px-3 md:w-3/12">
    <x-form.input name="barcode" label="Barcode" :value="$copy->barcode" required icon="fas fa-barcode" />
  </div>
  <div class="w-full max-w-full px-3 md:w-3/12">
    <x-form.input name="inventory_code" label="Kode inventaris" :value="$copy->inventory_code" />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="acquisition_date" label="Tanggal perolehan" type="date" :value="optional($copy->acquisition_date)->format('Y-m-d')" />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="source" label="Sumber" :value="$copy->source" help="Pembelian, hibah, dll." />
  </div>
  <div class="w-full max-w-full px-3 md:w-2/12">
    <x-form.select name="condition" label="Kondisi" :options="$conditionOptions" :selected="$copy->condition?->value" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-2/12">
    <x-form.select name="status" label="Status" :options="$statusOptions" :selected="$copy->status?->value" required />
  </div>
  <div class="w-full max-w-full px-3">
    <x-form.input name="notes" label="Catatan" :value="$copy->notes" />
  </div>
</div>
