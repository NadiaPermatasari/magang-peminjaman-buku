# AGENTS.md — Panduan untuk AI Coding Agent

File ini ditujukan untuk AI coding agent (Codex, Cursor, Copilot, Gemini, dll.) yang
diminta membantu mengerjakan repo ini. Baca sampai habis sebelum mengubah kode.
Isinya ringkas dan diverifikasi terhadap kode; detail lengkap ada di folder [docs/](docs/).

> Bahasa: teks UI, flash message, pesan exception, dan label dalam **Bahasa Indonesia**.
> Nama class/method/kolom/route dalam **Bahasa Inggris** (kecuali kolom `no_hak`, `no_su`,
> `nama_pemilik`). Komentar kode boleh Indonesia atau Inggris, ikuti file yang diedit.

---

## 1. Aplikasi ini apa

**Sistem Informasi Peminjaman Buku** — aplikasi web Laravel untuk mengelola peminjaman
**arsip berkas pertanahan** (awalnya katalog buku perpustakaan, lalu dialihkan ke berkas hak
tanah). Fitur: master data berkas + eksemplar fisik, keanggotaan, alur pengajuan →
verifikasi → serah terima → pengembalian (dengan bukti foto), perpanjangan pinjaman,
notifikasi (in-app, email, WhatsApp/Fonnte), laporan + ekspor CSV, RBAC, 2FA opsional,
enkripsi data sensitif, audit trail, landing page publik + SEO.

**PENTING — nama legacy:** entitas "berkas" di kode tetap bernama `Book` / tabel `books` /
route `books.*`; "Jenis Hak" tetap `Category` / `categories`. Hanya label UI dan kolomnya
yang berubah. **Jangan rename** class/tabel/route ini.

| Label di UI | Di kode |
|---|---|
| Buku / Berkas | `Book`, `books` |
| Eksemplar | `BookCopy`, `book_copies` (barcode unik per kopi fisik) |
| Jenis Hak (Hak Milik, HGU, Hak Pakai, Hak Pengelolaan, Lainnya) | `Category`, `categories` |
| No. Hak / No. SU / Nama Pemilik | `books.no_hak` / `books.no_su` / `books.nama_pemilik` |
| Pengajuan / Peminjaman | `Loan` (header) + `LoanItem` (per buku) |
| Perpanjangan / Banding | `LoanExtension` |
| Anggota | `Member` (opsional tertaut ke `User` lewat `user_id`) |
| Grup menu "Pengajuan & Perpanjangan Peminjam" | dulu "Transaksi" |

---

## 2. Stack & perintah

- PHP ^8.2 (dikembangkan dengan 8.4), **Laravel 12**, `laravel/fortify` (auth + 2FA),
  `spatie/laravel-permission` (RBAC). Tidak ada Livewire/Inertia/Vue/React.
- Frontend: **Blade + Tailwind CSS 3** dengan tema **Argon Dashboard Tailwind**
  (aset statis di `public/assets/`), build via Vite. Chart.js untuk grafik.
- DB: MySQL/MariaDB (dev/prod). Test: **SQLite in-memory** (lihat `phpunit.xml`).
- Queue: driver `database`. Test: PHPUnit 11 (bukan Pest). Code style: Laravel Pint.
- Dev environment pemilik repo: Windows.

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate   # isi juga BLIND_INDEX_KEY
php artisan migrate --seed          # atau migrate:fresh --seed untuk reset dev
php artisan storage:link
composer run dev                    # serve + queue:listen + pail + vite sekaligus
php artisan test                    # wajib lulus sebelum selesai
php artisan test --filter=LoanFlowTest
vendor/bin/pint                     # format kode PHP (preset default Laravel)
npm run build                       # build aset produksi
```

Seeder (`DatabaseSeeder`): `RolePermissionSeeder` → `SuperAdminSeeder` →
`ApplicationSettingSeeder` → `DemoMasterDataSeeder` (dilewati saat `APP_ENV=production`).

---

## 3. Peta direktori

```
app/
├── Actions/Loans/        Semua business logic peminjaman (CreateLoan, ApproveLoan, RejectLoan,
│                         HandoverLoan, ReturnLoan, ExpireLoan, CancelLoan,
│                         RequestLoanExtension, ApproveLoanExtension, RejectLoanExtension)
├── Console/Commands/     loans:mark-overdue, loans:expire-approved, loans:process-reminders
│                         (dijadwalkan per jam di routes/console.php, semuanya idempoten)
├── Enums/                LoanStatus, ExtensionStatus, BookCopyStatus, BookCondition, MemberStatus
│                         — label(), badgeColor(), transitions(), canTransitionTo()
├── Exceptions/           LoanException (pelanggaran aturan bisnis → flash error, bukan 500)
├── Http/Controllers/     Controller tipis
├── Http/Middleware/      SecurityHeaders (CSP dll.), SyncOverdueLoans
├── Http/Requests/        Satu FormRequest per operasi tulis (authorize() + rules())
├── Models/               Eloquent; cast enum & encrypted
├── Notifications/        Satu class per kejadian; Channels/FonnteChannel; Concerns/SendsLibraryNotifications
├── Policies/             Satu per model (auto-discovered), cek object-level / IDOR
└── Support/              Activity (audit log), BlindIndex, Color, CsvExport, UserAgent,
                          Concerns/HasUuid, helpers.php (setting(), app_name(), max_active_loans())
resources/views/
├── layouts/              base, app (halaman login), auth, public; partials/sidenav.blade.php (menu)
├── components/           x-card, x-button, x-stat-card, x-auth-card, x-form.* (input, select, ...)
└── <modul>/              index / create / edit / _form per modul
routes/web.php            Semua route web (Fortify mendaftarkan route auth sendiri)
tests/Feature/            Feature test; fixture bersama di tests/Concerns/WithLibraryData.php
docs/                     Dokumentasi lengkap (arsitektur, keamanan, permission, alur pinjam)
```

---

## 4. Pola wajib (ikuti persis)

Alur request standar:

```
Route (middleware auth, opsional permission:xxx)
  → FormRequest   authorize() memanggil Policy, rules() memvalidasi
  → Controller    tipis: $this->authorize(...) bila tanpa FormRequest, panggil Action
  → Action        DB::transaction, cek transisi enum, lockForUpdate bila alokasi stok
  → Activity::log('KODE_AKSI', 'deskripsi', $model)   ← audit trail
  → $user->notify(new XxxNotification(...))           ← queued
  ← redirect()->...->with('success' | 'error', 'Pesan Bahasa Indonesia')
```

Aturan:

1. **Business logic di Action class** (`app/Actions/...`, method `handle()`), bukan di controller.
   Untuk CRUD master data sederhana, logic di controller masih diterima (lihat `BookController`).
2. **Validasi lewat FormRequest**; pakai `$request->validated()` / `$request->safe()`.
   **Jangan pernah** `Model::create($request->all())`. Field sensitif (role, status, `created_by`)
   di-set eksplisit di controller/Action.
3. **Otorisasi berbasis permission**, bukan nama role: `$user->can('loans.approve')`,
   `@can('update', $book)`, Policy. **Jangan** `if ($user->hasRole('petugas'))`. Tidak ada
   `Gate::before` untuk super-admin — super-admin lolos karena semua permission di-seed.
4. **Policy untuk object-level**: resource milik anggota (`Loan`, `LoanExtension`, `Member`) wajib
   cek kepemilikan, contoh di `LoanPolicy::view()`: `view-all` ATAU `$loan->member->user_id === $user->id`.
5. **Transisi status** hanya lewat `Enum::canTransitionTo()`; jika tidak valid, lempar
   `LoanException` dengan pesan Indonesia. Controller menangkapnya:
   `catch (LoanException $e) { return back()->with('error', $e->getMessage()); }`.
6. **Audit**: setiap aksi tulis penting memanggil `App\Support\Activity::log()`. Kode aksi
   UPPER_SNAKE (`BOOK_CREATED`, `LOAN_APPROVED`). Ikon/warna per kode ada di
   `ActivityLog` model — tambahkan entri di sana untuk kode baru. **Jangan** masukkan password,
   OTP, secret 2FA, recovery code, atau token ke log.
7. **Pengaturan aplikasi** dibaca via `setting('key', default)`; default ada di
   `Setting::$defaults`. Jangan hardcode aturan bisnis (durasi pinjam, batas perpanjangan, dst.).
   Menambah setting baru = tambah ke `$defaults` + form `settings/edit.blade.php` +
   validasi di `SettingController` + `ApplicationSettingSeeder` bila perlu.
8. **UUID di URL**: model publik memakai trait `HasUuid` (route key = `uuid`). Kolom `id`
   tetap integer internal untuk relasi. UUID bukan pengganti Policy.
9. **Upload file**: validasi `image|mimes:jpg,jpeg,png,webp|max:...` (SVG dilarang),
   simpan `->store('<folder>', 'public')` (nama acak), hapus file lama saat diganti,
   pasang `->middleware('throttle:upload')` di route-nya.
10. **Pagination**: `->paginate((int) setting('per_page', 10))->withQueryString()`, tampilkan
    dengan `$items->links('pagination.argon')`.
11. **Data sensitif anggota**: `phone`, `address`, `identity_number` memakai cast `encrypted`.
    NIK **wajib** di-set lewat `$member->setIdentityNumber($nik)` (sinkron blind index
    `identity_number_index`); cari NIK via `Member::findByIdentityNumber()`. Kolom
    terenkripsi tidak bisa di-`WHERE`/`LIKE`.
12. **Konkurensi**: alokasi eksemplar dan pemrosesan item memakai `lockForUpdate()` di dalam
    transaksi. Kode peminjaman `PJ-YYYYMMDD-000001` dari tabel `loan_number_sequences`.

---

## 5. Domain & alur peminjaman

```
PENDING ──approve──► APPROVED ──handover (per eksemplar)──► BORROWED ──return──► RETURNED
   │                    │                                     │   ▲
   ├─reject─► REJECTED  ├─lewat pickup_deadline─► EXPIRED     │   │ perpanjangan disetujui
   └─cancel─► CANCELLED └─cancel─► CANCELLED                  ▼   │
                                                           OVERDUE ──return──► RETURNED
```

- **Submit** (`CreateLoan`): cek anggota aktif, batas `max_active_loans()` (1–2 buku),
  `block_if_overdue`, ketersediaan eksemplar. **Tidak** mengalokasikan eksemplar.
- **Approve** (`ApproveLoan`): kunci & reservasi 1 eksemplar `AVAILABLE` per item →
  `RESERVED`, set `pickup_deadline`.
- **Handover** (`HandoverLoan`): per barcode eksemplar; foto bukti opsional (bisa diunggah
  menyusul). Item terakhir diserahkan → loan `BORROWED`, `due_at` = now + `loan_duration_days`.
- **Return** (`ReturnLoan`): petugas **memilih** eksemplar dari daftar (bukan scan barcode),
  **foto wajib**; status eksemplar mengikuti `BookCondition` (GOOD→AVAILABLE,
  MINOR_DAMAGE→MAINTENANCE, MAJOR_DAMAGE→DAMAGED, LOST→LOST).
- **Overdue**: `Loan::syncOverdue()` dipanggil scheduler **dan** middleware `SyncOverdueLoans`
  (maks 1×/menit) — status tetap benar walau cron belum terpasang.
- **Perpanjangan**: hanya saat BORROWED/OVERDUE; dibatasi `allow_renewal` & `max_renewals`.
  Disetujui → `due_at` baru = max(due_at lama, hari ini) + hari disetujui; OVERDUE kembali BORROWED.
- **Tidak ada fitur denda** (tabel `fines` sudah di-drop). Modul penulis/penerbit sudah dihapus.
  Buku **tidak lagi** punya rak/edisi/bahasa/jumlah halaman (di-drop 2026-10-17); master
  data Rak masih ada sebagai modul tersendiri.

Detail: [docs/loan-flow.md](docs/loan-flow.md).

---

## 6. RBAC

Role bawaan: `super-admin`, `admin-perpustakaan`, `petugas`, `anggota`, `pimpinan`.
Daftar permission & pemetaan role adalah **sumber kebenaran tunggal** di
[database/seeders/RolePermissionSeeder.php](database/seeders/RolePermissionSeeder.php)
(ringkasan di [docs/permissions.md](docs/permissions.md)).

Menambah permission baru:
1. Tambahkan ke `PERMISSIONS` dan ke role terkait di `ROLE_PERMISSIONS`.
   ⚠️ Seeder **menghapus** permission yang tidak ada di daftar — jangan hapus entri yang masih dipakai.
2. Jalankan `php artisan db:seed --class=RolePermissionSeeder` (idempoten).
3. Pakai di route (`permission:xxx`), Policy, dan `@can` di Blade/sidenav.

Role bawaan tidak bisa dihapus/di-rename dan permission `super-admin` tidak bisa diubah —
diproteksi di `RoleController`. Super Admin bisa membuat role kustom via UI.

---

## 7. UI / Blade

- Halaman login-area: `@extends('layouts.app')`, `@section('title', '...')`,
  `@section('page', '...')`, `@section('content')`. Script/style tambahan via `@push('scripts')`.
- Gunakan komponen yang ada sebelum menulis markup baru:
  `<x-card title subtitle :padding="false">` (+ `<x-slot:actions>`),
  `<x-button href|type variant icon>`, `<x-form.input|select|textarea|checkbox|toggle|file|dropzone>`.
  Contoh tabel + filter + aksi: [resources/views/books/index.blade.php](resources/views/books/index.blade.php).
- Form create/edit berbagi partial `_form.blade.php`.
- Selalu `{{ }}` (auto-escape). Jangan `{!! !!}` untuk data dari user. Form tulis wajib `@csrf`.
- Sembunyikan tombol dengan `@can`, tapi **otorisasi tetap di backend** (Policy/FormRequest).
- Menu sidebar: tambahkan item array di
  [resources/views/layouts/partials/sidenav.blade.php](resources/views/layouts/partials/sidenav.blade.php)
  (`label, route, active, icon, color, permission`). Item otomatis tersembunyi bila route belum ada
  atau user tidak punya permission.
- Warna primary dinamis lewat CSS variable `--color-primary` (lihat `tailwind.config.js`,
  `app/Support/Color.php`) — jangan hardcode warna brand baru.
- Ikon: Nucleo (`ni ni-...`) dan Font Awesome (`fas fa-...`).
- Mobile: gunakan `min-w-0` untuk kolom yang menyusut dan tumpuk form pada layar
  kecil. Tabel lebar memakai `overflow-x-auto` (petunjuk geser/fokus keyboard
  ditambahkan oleh `resources/js/responsive.js`). Jangan pasang Perfect Scrollbar
  pada halaman/tabel atau menyembunyikan overflow body untuk menutupi layout yang
  melebar. Sidebar mobile memakai backdrop, penguncian gulir, dan penahanan fokus;
  pertahankan breakpoint desktop 1200 px. Audit: [docs/mobile-audit.md](docs/mobile-audit.md).
- PWA: manifest publik `pwa.manifest`, halaman publik `pwa.offline`, worker
  `public/sw.js`, dan kontrol pemasangan di layout base. Cache Storage hanya boleh
  berisi halaman offline tanpa sesi dan ikon bawaan; jangan cache HTML/data setelah
  login, unggahan, atau respons transaksi. Naikkan versi cache worker jika halaman
  offline/ikon berubah. Jalankan juga `npm run test:pwa` dan `npm run build` untuk
  perubahan PWA. Detail: [docs/pwa.md](docs/pwa.md).

---

## 8. Notifikasi

- Satu class per kejadian di `app/Notifications/`, `implements ShouldQueue`,
  `use Queueable, SendsLibraryNotifications;`, `via()` mengembalikan `$this->libraryChannels()`.
- Wajib punya `toDatabase()` (array `title, message, icon, url, color`) dan `toMail()`.
  Teks WhatsApp otomatis dari `toDatabase()`.
- Channel: database selalu; mail bila `email_notification_enabled`; WhatsApp (Fonnte) bila
  `whatsapp_notification_enabled` **dan** `FONNTE_TOKEN` terisi. Gagal kirim WA hanya di-log,
  tidak boleh menggagalkan channel lain.
- Notifikasi ke staf: `User::permission('loans.approve')->get()->each(fn ($u) => $u->notify(...))`.

---

## 9. Testing

- Semua test di `tests/Feature/`, `use RefreshDatabase, WithLibraryData;`.
- Helper fixture: `$this->seedRoles()`, `$this->makeUser('petugas')`,
  `$this->makeMember($user)`, `$this->makeBookWithCopy()`. Action dipanggil via
  `app(ApproveLoan::class)->handle(...)`.
- Nama method: `test_snake_case_menjelaskan_perilaku(): void`.
- **Menambah halaman baru → tambahkan URI-nya ke `PageSmokeTest::pageProvider()`.**
- Fitur yang menyentuh akses wajib dites: user tanpa permission dapat 403, anggota lain
  (IDOR) dapat 403, UUID acak dapat 404. Contoh: `RbacTest`, `IdorTest`.
- Queue di test = `sync`, mail = `array`; pakai `Notification::fake()` / `Http::fake()` bila perlu.

---

## 10. Larangan & keamanan (jangan dilanggar)

- Jangan commit `.env`, secret, token Fonnte, atau kredensial. Jangan taruh secret di tabel `settings`.
- Jangan menyamakan `APP_KEY`, `BLIND_INDEX_KEY`, dan `DB_PASSWORD`.
- Jangan menambah cast `encrypted` pada `two_factor_secret`/`two_factor_recovery_codes`
  (sudah dienkripsi Fortify — double encryption).
- Jangan mengaktifkan registrasi publik Fortify.
- Jangan melonggarkan CSP di `SecurityHeaders` tanpa alasan jelas; CDN baru harus ditambahkan
  eksplisit ke CSP.
- Aksi sensitif (kelola user, role, pengaturan) memakai `password.confirm` di route **GET dan
  POST/PUT** sekaligus — pertahankan pola ini untuk route sejenis.
- Raw SQL hanya ekspresi tetap; input user selalu lewat binding Eloquent/Query Builder.
- `activity_logs` append-only: jangan buat fitur edit/hapus log.
- Jangan menambah dependency Composer/npm tanpa kebutuhan jelas (contoh: ekspor pakai CSV
  native `App\Support\CsvExport`, bukan library spreadsheet).
- Jangan mengubah migration lama yang sudah dijalankan; buat migration baru
  (format nama `YYYY_MM_DD_HHMMSS_deskripsi.php`), lengkap dengan `down()`.

---

## 11. Jebakan yang sering terjadi

- `PROMPT_CODE_AGENT_SISTEM_PEMINJAMAN_BUKU_LARAVEL.md` adalah **spec awal** (rujukan "spec §NN"
  di komentar kode). Sebagian sudah **usang**: denda, penulis/penerbit, ISBN, 2FA wajib untuk
  staf, scan barcode saat pengembalian. Bila spec bertentangan dengan kode/AGENTS.md, ikuti kode.
- README & `docs/DOKUMENTASI_APLIKASI.md` masih menyebut "rak" pada form buku — sudah tidak
  berlaku; `books.rack_id` telah di-drop.
- Di `routes/web.php`, segmen statis `loans/pending|ready|active|overdue|create` harus didaftarkan
  **sebelum** `loans/{loan}`.
- Akun staf tidak punya relasi `member`; halaman anggota harus menangani `$user->member === null`
  (lihat `LoanController::index()`), staf mengajukan "atas nama anggota".
- `Setting` di-cache selamanya (`Cache::rememberForever('settings')`); gunakan `Setting::set()` /
  `setMany()` agar cache dibersihkan. Di test, `CACHE_STORE=array`.
- Setelah mengubah permission, cache spatie perlu di-reset (seeder sudah melakukannya).
- Nilai enum disimpan sebagai string UPPERCASE (`'AVAILABLE'`, `'PENDING'`); bandingkan
  dengan instance enum, bukan string, setelah di-cast.

---

## 12. Checklist sebelum menyatakan selesai

- [ ] `php artisan test` lulus semua (tambahkan test untuk perilaku baru).
- [ ] `vendor/bin/pint` dijalankan pada file PHP yang diubah.
- [ ] Route baru punya otorisasi backend (permission middleware dan/atau Policy).
- [ ] Halaman baru terdaftar di `PageSmokeTest` dan (jika perlu) di sidenav.
- [ ] Aksi tulis baru tercatat di `Activity::log()`.
- [ ] Teks UI/flash/exception dalam Bahasa Indonesia.
- [ ] Migration baru (bukan edit lama) dan `php artisan migrate` berhasil.
- [ ] Bila perilaku/fitur berubah, perbarui `docs/` yang relevan dan bagian terkait di file ini.

---

## 13. Referensi lanjut

- [README.md](README.md) — instalasi, env, queue/scheduler, deployment
- [docs/architecture.md](docs/architecture.md) — struktur & keputusan desain
- [docs/loan-flow.md](docs/loan-flow.md) — alur peminjaman/pengembalian/perpanjangan
- [docs/permissions.md](docs/permissions.md) — permission & role
- [docs/security.md](docs/security.md) — kontrol keamanan (OWASP ASVS L2)
- [docs/pwa.md](docs/pwa.md) — instalasi PWA, halaman offline, cache, dan deployment
- [docs/mobile-audit.md](docs/mobile-audit.md) — temuan, perbaikan, dan verifikasi responsivitas
- [docs/DOKUMENTASI_APLIKASI.md](docs/DOKUMENTASI_APLIKASI.md) — dokumentasi lengkap satu file
