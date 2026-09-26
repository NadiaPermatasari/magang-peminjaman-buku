@php
  $isEdit = $member->exists;
  $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
@endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="name" label="Nama lengkap" :value="$member->name" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="email" label="Email" type="email" :value="$member->email" icon="fas fa-envelope" />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="phone" label="Telepon" :value="$member->phone" icon="fas fa-phone" />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="identity_number" label="NIK / No. Identitas" :value="$member->identity_number" help="Disimpan terenkripsi." />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.select name="status" label="Status" :options="$statusOptions" :selected="$member->status?->value" required />
  </div>
  <div class="w-full max-w-full px-3">
    <x-form.input name="address" label="Alamat" :value="$member->address" icon="fas fa-map-marker-alt" />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="joined_at" label="Tanggal bergabung" type="date" :value="optional($member->joined_at)->format('Y-m-d')" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="expired_at" label="Berlaku sampai" type="date" :value="optional($member->expired_at)->format('Y-m-d')" />
  </div>
  <div class="w-full max-w-full px-3 md:w-4/12">
    <x-form.input name="notes" label="Catatan" :value="$member->notes" />
  </div>

  @unless ($isEdit)
    <div class="w-full max-w-full px-3">
      <x-form.toggle name="create_login" label="Buatkan akun login untuk anggota ini" help="Anggota akan menerima email untuk mengatur kata sandi (email wajib diisi)." />
    </div>
  @endunless

  @if ($isEdit && $member->user)
    <div class="w-full max-w-full px-3">
      <p class="mt-1 mb-4 text-xs text-slate-400"><i class="mr-1 fas fa-user-check text-emerald-500"></i>Anggota ini memiliki akun login: {{ $member->user->email }}</p>
    </div>
  @endif
</div>
