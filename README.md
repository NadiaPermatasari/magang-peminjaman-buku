# Sistem Informasi Peminjaman Buku

Sistem informasi perpustakaan berbasis Laravel 12 untuk mengelola katalog buku, eksemplar fisik, keanggotaan, alur peminjaman/pengembalian, denda keterlambatan, notifikasi, laporan, dan keamanan aplikasi (RBAC, 2FA, enkripsi data sensitif, audit trail).

Tampilan menggunakan [Argon Dashboard Tailwind](https://www.creative-tim.com/product/argon-dashboard-tailwind) (Creative Tim), diintegrasikan dengan Laravel Fortify untuk autentikasi.

## Daftar Isi

- [Requirement](#requirement)
- [Instalasi](#instalasi)
- [Environment (`.env`)](#environment-env)
- [Migration & Seeding](#migration--seeding)
- [Membuat Akun Super Admin](#membuat-akun-super-admin)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Queue & Scheduler](#queue--scheduler)
- [Storage & Upload](#storage--upload)
- [Autentikasi & 2FA](#autentikasi--2fa)
- [RBAC (Role & Permission)](#rbac-role--permission)
- [Menjalankan Test](#menjalankan-test)
- [Backup](#backup)
- [Deployment Produksi](#deployment-produksi)
- [Catatan Keamanan](#catatan-keamanan)
- [Dokumentasi Lanjutan](#dokumentasi-lanjutan)

## Requirement

- PHP ^8.2 (proyek ini dikembangkan dengan PHP 8.4)
- Composer 2.x
- MySQL 8.x atau MariaDB 10.x
- Node.js 18+ dan npm (untuk build asset Tailwind)
- Ekstensi PHP standar Laravel (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`)

## Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## Environment (`.env`)

Selain variabel standar Laravel, proyek ini menambahkan beberapa key khusus — lihat `.env.example` untuk daftar lengkap dan komentarnya:

| Variabel | Keterangan |
|---|---|
| `DB_*` | Koneksi MySQL/MariaDB |
| `BLIND_INDEX_KEY` | Kunci HMAC terpisah dari `APP_KEY`, dipakai untuk blind-index pencarian pada field terenkripsi (contoh: NIK anggota). **Wajib diisi**, generate dengan: `php artisan tinker --execute="echo base64_encode(random_bytes(32));"` |
| `SUPER_ADMIN_NAME` / `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` | Dipakai sekali oleh `SuperAdminSeeder`. Kosongkan `SUPER_ADMIN_PASSWORD` agar seeder membuat password acak dan menampilkannya di output (bukan password lemah yang di-hardcode) |

Jangan pernah meng-commit `.env` — sudah masuk `.gitignore`. `APP_KEY`, `BLIND_INDEX_KEY`, dan kredensial database **harus berbeda** antar environment dan tidak boleh digunakan ulang untuk keperluan lain (spec keamanan §11).

## Migration & Seeding

```bash
php artisan migrate
php artisan db:seed
```

`db:seed` menjalankan berurutan:

1. `RolePermissionSeeder` — membuat seluruh permission (lihat [docs/permissions.md](docs/permissions.md)) dan 5 role default (`super-admin`, `admin-perpustakaan`, `petugas`, `anggota`, `pimpinan`). Idempotent, aman dijalankan berulang.
2. `SuperAdminSeeder` — membuat akun super-admin awal dari `SUPER_ADMIN_EMAIL`/`SUPER_ADMIN_PASSWORD`.
3. `ApplicationSettingSeeder` — mengisi nilai default untuk seluruh pengaturan aplikasi (identitas, branding, peminjaman, denda, notifikasi).
4. `DemoMasterDataSeeder` — data contoh (kategori, penulis, penerbit, rak, buku + eksemplar, anggota). **Otomatis dilewati saat `APP_ENV=production`.**

Untuk reset penuh saat development:

```bash
php artisan migrate:fresh --seed
```

## Membuat Akun Super Admin

Isi `SUPER_ADMIN_EMAIL` (dan opsional `SUPER_ADMIN_PASSWORD`) di `.env` sebelum menjalankan seeder pertama kali. Jika `SUPER_ADMIN_PASSWORD` dikosongkan, password acak akan dicetak sekali di terminal — segera login dan ganti password. Mengaktifkan 2FA sangat disarankan untuk role ini meskipun tidak diwajibkan sistem.

Untuk membuat/menambah super-admin lain setelah instalasi awal, jalankan ulang seeder ini dengan email baru:

```bash
php artisan db:seed --class=SuperAdminSeeder
```

## Menjalankan Aplikasi

```bash
php artisan serve
npm run dev   # atau `npm run build` untuk produksi
```

Atau jalankan server + queue worker + log viewer + Vite sekaligus:

```bash
composer run dev
```

## Queue & Scheduler

Notifikasi (email + in-app) dikirim lewat queue (`QUEUE_CONNECTION=database` secara default). Jalankan worker:

```bash
php artisan queue:work
```

Scheduler menjalankan tiga command setiap jam (lihat `routes/console.php`), semuanya idempotent — aman dijalankan berulang tanpa mengirim notifikasi duplikat:

- `loans:mark-overdue` — menandai peminjaman yang melewati jatuh tempo
- `loans:expire-approved` — membatalkan pengajuan yang disetujui tapi tidak diambil sebelum `pickup_deadline`
- `loans:process-reminders` — mengirim reminder pengambilan/jatuh tempo/keterlambatan sesuai pengaturan Notifikasi

Di production, daftarkan satu entri cron:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Gunakan Supervisor/systemd untuk menjaga `queue:work` tetap berjalan — contoh konfigurasi Supervisor:

```ini
[program:peminjaman-buku-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
stopwaitsecs=3600
```

## Storage & Upload

```bash
php artisan storage:link
```

File upload (sampul buku, logo/favicon/background branding, avatar) disimpan di `storage/app/public`, divalidasi MIME dari isi file (bukan ekstensi kiriman klien), dibatasi ukurannya, dan diberi nama acak (spec §22) — SVG tidak diizinkan.

## Autentikasi & 2FA

- Registrasi publik **dinonaktifkan** (spec §58) — semua akun dibuat oleh admin lewat menu *Administrasi > User* (staf) atau *Keanggotaan > Anggota* (anggota, dengan opsi "buatkan akun login").
- 2FA (TOTP) bersifat **opsional untuk semua role** — pengguna dapat mengaktifkannya sendiri lewat halaman Profil kapan saja, dan menonaktifkannya kembali tanpa batasan.
- Perubahan role/permission, menonaktifkan 2FA, membuat ulang kode pemulihan, dan mengubah Pengaturan wajib konfirmasi ulang password (spec §6).

## RBAC (Role & Permission)

Lihat [docs/permissions.md](docs/permissions.md) untuk daftar lengkap permission dan pemetaan ke role. Otorisasi diperiksa di dua lapis: middleware (`permission:`) di routing, **dan** Laravel Policy per model untuk pemeriksaan object-level (mencegah IDOR — lihat [docs/security.md](docs/security.md)).

## Halaman Depan (Landing Page) & SEO

`/` menampilkan landing page publik untuk pengunjung yang belum login (pengguna yang sudah login otomatis diarahkan ke dashboard). Kontennya (judul & subjudul hero, gambar hero, teks "tentang", meta description) diatur Super Admin lewat **Pengaturan Aplikasi > Halaman Depan** — jumlah buku/kategori/anggota dan daftar koleksi terbaru selalu diambil live dari database.

SEO yang sudah diterapkan:

- `<title>`, meta description, canonical URL, Open Graph, dan Twitter card otomatis di setiap halaman (lihat `resources/views/layouts/base.blade.php`)
- Data terstruktur JSON-LD (`schema.org/Library`) di landing page
- `meta robots` bernilai `index, follow` hanya di halaman publik; seluruh halaman aplikasi (di balik login) otomatis `noindex, nofollow`
- `/robots.txt` dan `/sitemap.xml` disajikan dinamis lewat `SeoController` (bukan file statis) agar URL-nya selalu mengikuti `APP_URL` yang sebenarnya

## Tampilan (Branding & Warna)

Selain logo/favicon/background, Super Admin dapat mengganti **warna utama (primary)** aplikasi lewat color picker di Pengaturan > Branding. Warna ini diterapkan lewat CSS variable (`--color-primary`, lihat `tailwind.config.js` dan `app/Support/Color.php`) sehingga seluruh tombol/link/status aktif ikut berubah **tanpa perlu build ulang asset** setiap kali warna diganti.

## Menjalankan Test

```bash
php artisan test
```

Test menggunakan SQLite in-memory (dikonfigurasi di `phpunit.xml`) — database MySQL development/production **tidak pernah tersentuh** oleh test suite. Cakupan: autentikasi, RBAC, IDOR, alur peminjaman penuh (termasuk cegah alokasi ganda eksemplar), denda, pengaturan, dan kontrol keamanan (CSRF, mass assignment, validasi upload, anggota nonaktif diblokir).

## Backup

Dokumentasikan dan uji strategi backup sesuai kebutuhan instansi:

- **Harian**: dump database (`mysqldump` terenkripsi), retensi 14–30 hari.
- **Mingguan**: backup penuh termasuk `storage/app/public` (file upload), retensi 3 bulan.
- **Bulanan**: arsip off-site/terpisah dari server produksi, retensi 1 tahun+.

Simpan key enkripsi backup **terpisah** dari lokasi backup itu sendiri. Uji proses restore secara berkala — backup yang belum pernah diuji restore-nya tidak bisa diandalkan.

## Deployment Produksi

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan db:seed --force   # hanya saat setup awal
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Checklist sebelum go-live:

- `APP_ENV=production`, `APP_DEBUG=false`
- `SESSION_SECURE_COOKIE=true` bila diakses via HTTPS (wajib untuk produksi nyata)
- Database menggunakan akun khusus aplikasi (bukan `root`), least-privilege, tidak diekspos ke internet publik
- HTTPS/TLS aktif di reverse proxy (Nginx/Apache) — jangan jalankan Laravel langsung ke internet tanpa TLS termination
- Queue worker (`queue:work`) berjalan di bawah Supervisor/systemd
- Cron `schedule:run` terpasang
- Web server **tidak** berjalan sebagai root
- `storage/` dan `bootstrap/cache/` writable oleh user web server, tapi tidak dapat dieksekusi sebagai script

## Catatan Keamanan

Ringkasan kontrol keamanan yang diimplementasikan ada di [docs/security.md](docs/security.md). Poin penting:

- Password di-hash (`bcrypt`), tidak pernah disimpan/di-log plaintext.
- Field sensitif (telepon, alamat, NIK anggota) dienkripsi (`encrypted` cast, `APP_KEY`); NIK memakai blind index terpisah (`BLIND_INDEX_KEY`) untuk pengecekan duplikat tanpa menyimpan nilai yang mudah ditebak.
- UUID dipakai di seluruh URL publik — **bukan pengganti otorisasi**; setiap resource tetap melalui Policy.
- Audit trail (`activity_logs`) bersifat append-only, tidak ada tombol edit/delete di UI.
- Rate limiting pada login, 2FA, forgot/reset password, pencarian barcode, dan endpoint upload.

## Dokumentasi Lanjutan

- [docs/architecture.md](docs/architecture.md) — struktur aplikasi, domain model, alasan keputusan desain
- [docs/security.md](docs/security.md) — kontrol keamanan detail per OWASP ASVS Level 2
- [docs/permissions.md](docs/permissions.md) — daftar permission dan pemetaan role
- [docs/loan-flow.md](docs/loan-flow.md) — diagram dan penjelasan alur peminjaman-pengembalian-denda
