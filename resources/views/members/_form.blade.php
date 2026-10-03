@php
  $isEdit = $member->exists;
  $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
@endphp

<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="name" label="Nama lengkap" :value="$member->name" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="email" label="Email" type="email" :value="$member->email" icon="fas fa-envelope" help="Dipakai sebagai username saat login." />
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
    {{-- Akun login dibuat langsung di sini: petugas menentukan kata sandinya
         dan menyerahkannya ke anggota, jadi anggota bisa login tanpa
         bergantung pada email verifikasi/reset. --}}
    <div class="w-full max-w-full px-3 mt-2">
      <hr class="h-px mx-0 mb-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:via-white" />
      <x-form.toggle name="create_login" label="Buatkan akun login untuk anggota ini" :checked="old('create_login', '1') === '1'" help="Anggota dapat masuk memakai email di atas dan kata sandi yang Anda tentukan." />
    </div>
    <div class="w-full max-w-full px-3 md:w-6/12">
      <x-form.input name="password" label="Kata sandi" type="password" autocomplete="new-password" help="Minimal 8 karakter." />
    </div>
    <div class="w-full max-w-full px-3 md:w-6/12">
      <x-form.input name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" />
    </div>
  @endunless

  @if ($isEdit)
    <div class="w-full max-w-full px-3">
      @if ($member->user)
        <p class="mt-1 mb-2 text-xs text-slate-400"><i class="mr-1 fas fa-user-check text-emerald-500"></i>Anggota ini memiliki akun login: {{ $member->user->email }}</p>
      @else
        <p class="mt-1 mb-2 text-xs text-slate-400"><i class="mr-1 fas fa-user-slash text-orange-500"></i>Anggota ini belum memiliki akun login.</p>
      @endif
    </div>
  @endif
</div>
