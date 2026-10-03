@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page', 'settings')

@section('content')
  <div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
      <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <x-card title="Identitas Aplikasi" class="mb-6">
          <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="app_name" label="Nama aplikasi" :value="$settings['app_name']" required help="Tampil di sidebar, judul halaman, dan footer." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="app_short_name" label="Nama singkat" :value="$settings['app_short_name']" />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="institution_name" label="Nama instansi" :value="$settings['institution_name']" />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="email" label="Email kontak" type="email" :value="$settings['email']" icon="fas fa-envelope" />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="phone" label="Telepon" :value="$settings['phone']" icon="fas fa-phone" />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.select name="timezone" label="Zona waktu" :options="array_combine($timezones, $timezones)" :selected="$settings['timezone']" searchable required />
            </div>
            <div class="w-full max-w-full px-3">
              <x-form.input name="address" label="Alamat" :value="$settings['address']" icon="fas fa-map-marker-alt" />
            </div>
            <div class="w-full max-w-full px-3">
              <x-form.textarea name="description" label="Deskripsi" :value="$settings['description']" rows="2" maxlength="500" counter />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="per_page" label="Jumlah data per halaman" type="number" min="5" max="100" :value="$settings['per_page']" required />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="footer_text" label="Teks footer" :value="$settings['footer_text']" />
            </div>
          </div>
        </x-card>

        <x-card title="Branding" subtitle="JPG/PNG/WEBP, favicon PNG. Ukuran dibatasi dan nama file diacak otomatis." class="mb-6">
          <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.file name="logo" label="Logo" accept="image/png,image/jpeg,image/webp" :preview="$settings['logo'] ? asset('storage/'.$settings['logo']) : null" help="Maks 1 MB." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.file name="login_logo" label="Logo halaman login" accept="image/png,image/jpeg,image/webp" :preview="$settings['login_logo'] ? asset('storage/'.$settings['login_logo']) : null" help="Maks 1 MB." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.file name="favicon" label="Favicon" accept="image/png" :preview="$settings['favicon'] ? asset('storage/'.$settings['favicon']) : null" help="PNG, maks 256 KB." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.file name="login_background" label="Background halaman login" accept="image/png,image/jpeg,image/webp" :preview="$settings['login_background'] ? asset('storage/'.$settings['login_background']) : null" help="Maks 2 MB." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.label for="primary_color" required>Warna utama (primary)</x-form.label>
              <div class="flex items-center gap-3">
                <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $settings['primary_color']) }}" class="w-14 h-10.5 p-1 border border-solid rounded-lg cursor-pointer border-gray-300 dark:border-white/20 dark:bg-slate-850" />
                <span class="text-sm text-slate-400">Warna tombol, link, dan elemen aktif di seluruh aplikasi.</span>
              </div>
              <x-form.error name="primary_color" />
            </div>
          </div>
        </x-card>

        <x-card title="Peminjaman" class="mb-6">
          <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.input name="loan_duration_days" label="Lama pinjam (hari)" type="number" min="1" :value="$settings['loan_duration_days']" required />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.input name="max_active_loans" label="Maks. pinjaman aktif" type="number" min="1" :value="$settings['max_active_loans']" required />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.input name="pickup_deadline_days" label="Batas pengambilan (hari)" type="number" min="1" :value="$settings['pickup_deadline_days']" required />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.input name="max_renewals" label="Maks. perpanjangan" type="number" min="0" :value="$settings['max_renewals']" required help="Berapa kali satu peminjaman boleh diperpanjang. 0 = tidak boleh." />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.toggle name="allow_renewal" label="Izinkan perpanjangan / banding" :checked="(bool) $settings['allow_renewal']" help="Anggota dapat mengajukan perpanjangan untuk disetujui petugas." />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.toggle name="block_if_overdue" label="Blokir jika ada keterlambatan" :checked="(bool) $settings['block_if_overdue']" />
            </div>
          </div>
        </x-card>

        <x-card title="Notifikasi" class="mb-6">
          <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.toggle name="email_notification_enabled" label="Aktifkan notifikasi email" :checked="(bool) $settings['email_notification_enabled']" />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.toggle
                name="whatsapp_notification_enabled"
                label="Aktifkan notifikasi WhatsApp (Fonnte)"
                :checked="(bool) $settings['whatsapp_notification_enabled']"
                :help="config('services.fonnte.token') ? null : 'FONNTE_TOKEN belum diatur di server — notifikasi WA tidak akan terkirim meski diaktifkan.'"
              />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.input name="due_reminder_days" label="Reminder jatuh tempo (H-, pisahkan koma)" :value="implode(', ', (array) $settings['due_reminder_days'])" help="Contoh: 3, 1, 0" />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.input name="overdue_reminder_days" label="Reminder keterlambatan (hari, pisahkan koma)" :value="implode(', ', (array) $settings['overdue_reminder_days'])" help="Contoh: 1, 3, 7" />
            </div>
            <div class="w-full max-w-full px-3 md:w-4/12">
              <x-form.input name="pickup_reminder_hours" label="Reminder pengambilan (jam sebelum kedaluwarsa)" type="number" min="1" :value="$settings['pickup_reminder_hours']" required />
            </div>
          </div>
        </x-card>

        <x-card title="Halaman Depan" subtitle="Konten landing page publik yang dilihat pengunjung sebelum masuk." class="mb-6">
          <x-slot:actions>
            <x-button type="submit" icon="fas fa-save">Simpan semua pengaturan</x-button>
          </x-slot:actions>
          <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="landing_hero_title" label="Judul utama (hero)" :value="$settings['landing_hero_title']" placeholder="{{ $settings['app_name'] }}" help="Kosongkan untuk memakai nama aplikasi." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.input name="landing_about_title" label="Judul bagian tentang" :value="$settings['landing_about_title']" />
            </div>
            <div class="w-full max-w-full px-3">
              <x-form.textarea name="landing_hero_subtitle" label="Subjudul (hero)" :value="$settings['landing_hero_subtitle']" rows="2" maxlength="255" counter />
            </div>
            <div class="w-full max-w-full px-3">
              <x-form.textarea name="landing_about_content" label="Isi bagian tentang" :value="$settings['landing_about_content']" rows="4" maxlength="2000" counter help="Ceritakan sekilas tentang perpustakaan/instansi Anda." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.file name="landing_hero_image" label="Gambar hero" accept="image/png,image/jpeg,image/webp" :preview="$settings['landing_hero_image'] ? asset('storage/'.$settings['landing_hero_image']) : null" help="Maks 2 MB." />
            </div>
            <div class="w-full max-w-full px-3 md:w-6/12">
              <x-form.textarea name="landing_meta_description" label="Meta description (SEO)" :value="$settings['landing_meta_description']" rows="2" maxlength="160" counter help="Ringkasan 1-2 kalimat yang tampil di hasil pencarian Google. Kosongkan untuk memakai Deskripsi di atas." />
            </div>
          </div>
        </x-card>
      </form>
    </div>
  </div>
@endsection
