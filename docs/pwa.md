# Progressive Web App (PWA)

Aplikasi dapat dipasang dari browser ke layar utama ponsel atau desktop dan
dibuka dalam jendela tersendiri (`standalone`). Implementasi memakai Web App
Manifest dan service worker native, tanpa dependency Composer/npm tambahan.

## Pemasangan

- **Android / Chrome:** buka website, lalu pilih **Pasang Aplikasi** ketika tombol
  tersedia. Alternatifnya, gunakan menu pemasangan di browser.
- **Desktop / Chrome atau Edge:** pilih **Pasang Aplikasi** atau ikon pemasangan
  di bilah alamat.
- **iPhone / iPad:** pilih **Pasang Aplikasi** untuk melihat panduan, lalu buka
  menu **Bagikan → Tambahkan ke Layar Utama**. Jika menu belum tersedia di browser
  yang digunakan, buka website melalui Safari.

Tombol pemasangan disembunyikan ketika aplikasi berjalan dalam mode standalone.
Menutup tawaran pemasangan menyembunyikannya selama sesi tab browser tersebut.
Kemunculan dialog pemasangan mengikuti dukungan dan keputusan browser.

Nama aplikasi, nama singkat, deskripsi, dan warna tema di manifest mengikuti
**Pengaturan** yang sudah tersedia. Ikon bawaan menggambarkan arsip berkas.
Sumber vektornya berada di `resources/pwa/icon.svg`; aset PNG tersedia dalam
ukuran 192×192, 512×512 (termasuk maskable), dan 180×180 untuk Apple Touch Icon.
Perubahan logo/favicon di Pengaturan tidak mengganti ikon PWA bawaan ini.
Browser dapat memerlukan waktu untuk memperbarui identitas aplikasi yang sudah
dipasang setelah Pengaturan berubah.

## Koneksi Terputus dan Keamanan Cache

Saat navigasi gagal karena jaringan, service worker menampilkan halaman
**Koneksi terputus**. Tombol **Coba Lagi** membuka ulang URL yang sedang dituju.
Halaman ini mandiri: tidak bergantung pada Vite, CDN, font, atau JavaScript.

Cache Storage milik PWA hanya berisi:

- Halaman publik `/offline`, diambil tanpa kredensial sesi.
- Tiga ikon PNG bawaan dalam `public/pwa/icons/`.

Halaman setelah login, data anggota, katalog, laporan, foto unggahan, token
CSRF, dan respons transaksi tidak ditulis ke Cache Storage oleh service worker.
Navigasi GET selalu mencoba server; respons server seperti 403, 404, atau 500
tetap diteruskan. POST/PUT/PATCH/DELETE, unggahan, dan panggilan API tidak
diintersep atau diantrekan. Pengajuan, persetujuan, dan pengembalian tetap
memerlukan koneksi internet.

## File dan Route

| Lokasi | Fungsi |
|---|---|
| `app/Http/Controllers/PwaController.php` | Manifest dinamis dan halaman offline publik |
| `/manifest.webmanifest` (`pwa.manifest`) | Identitas, ikon, scope, dan URL awal `/dashboard` |
| `/offline` (`pwa.offline`) | Halaman cadangan tanpa data pengguna |
| `public/sw.js` | Precache terbatas dan penanganan navigasi saat jaringan gagal |
| `resources/js/pwa.js` | Registrasi worker, tombol pemasangan, dan panduan iOS |
| `resources/views/components/pwa-install.blade.php` | Kontrol pemasangan bersama |
| `resources/views/layouts/base.blade.php` | Manifest dan metadata PWA pada seluruh layout |

Manifest dan halaman offline sengaja dapat diakses tanpa login. URL awal
`/dashboard` tetap memakai autentikasi yang sudah ada; pengguna tanpa sesi
diarahkan ke halaman masuk. CSP secara eksplisit membatasi `manifest-src` dan
`worker-src` ke `'self'`. URL worker dan ikon menggunakan origin aplikasi,
termasuk bila `ASSET_URL` diarahkan ke CDN.

## Menjalankan dan Memperbarui

Pemasangan dan service worker memerlukan **HTTPS**. Untuk pengembangan lokal,
browser juga mengizinkan `http://localhost` dan `http://127.0.0.1`. Domain
Laragon seperti `http://peminjaman-buku.test` memerlukan HTTPS atau pengujian
melalui localhost. Mengakses HTTP dari IP LAN ponsel tidak memenuhi syarat.

```bash
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Gunakan DevTools → **Application → Manifest / Service Workers / Cache Storage**
untuk memeriksa manifest, aktivasi worker, dan isi cache. Buka satu halaman
secara online terlebih dahulu sampai worker aktif sebelum mencoba navigasi
dengan jaringan dinonaktifkan.

Saat halaman offline atau ikon berubah, naikkan versi `CACHE_NAME` di
`public/sw.js`. Cache versi lama milik scope aplikasi dibersihkan saat worker
baru aktif. Worker baru menunggu tab aplikasi lama ditutup; tidak ada reload
otomatis yang dapat mengganggu formulir yang sedang diisi.

Deployment menggunakan proses Laravel biasa dan `npm run build`. Pastikan
`public/sw.js` dan `public/pwa/icons/` ikut diunggah, origin worker sama dengan
website, dan proxy/CDN tidak menahan versi lama `sw.js`. Fitur ini tidak
memerlukan migration, perubahan role, atau konfigurasi queue baru.

## Verifikasi

```bash
php artisan test
npm run test:pwa
npm run build
```

`PwaTest` menguji manifest, identitas dari Pengaturan, ukuran ikon, integrasi
layout, CSP, serta halaman offline tanpa data sesi. `PwaServiceWorker.test.cjs`
menguji batas cache, kegagalan jaringan, respons server, metode tulis, isolasi
scope, dan pembersihan cache. Halaman `/offline` juga terdaftar di `PageSmokeTest`.

Rujukan: [panduan pemasangan PWA MDN](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Making_PWAs_installable)
dan [penggunaan service worker MDN](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API/Using_Service_Workers).
