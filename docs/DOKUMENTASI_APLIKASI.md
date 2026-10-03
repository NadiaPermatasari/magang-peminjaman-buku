# Dokumentasi Aplikasi — Sistem Informasi Peminjaman Buku

Dokumen ini merangkum **seluruh** aplikasi dalam satu file: apa aplikasi ini, fitur-fiturnya, bagaimana kodenya disusun, dan — secara khusus dan mendalam — kontrol keamanan siber (cybersecurity) serta enkripsi data yang dipakai. Bagian 1–12 adalah rangkuman yang bisa dibaca berdiri sendiri; §13 (Lampiran) menyalin **utuh** isi `docs/architecture.md`, `docs/security.md`, `docs/permissions.md`, `docs/loan-flow.md`, dan `README.md`, sehingga file ini benar-benar lengkap tanpa perlu membuka file lain.

---

## Daftar Isi

1. [Tentang Aplikasi](#1-tentang-aplikasi)
2. [Fitur-Fitur Utama](#2-fitur-fitur-utama)
3. [Teknologi yang Digunakan](#3-teknologi-yang-digunakan)
4. [Arsitektur & Struktur Kode](#4-arsitektur--struktur-kode)
5. [Struktur Basis Data](#5-struktur-basis-data)
6. [Role & Hak Akses (RBAC)](#6-role--hak-akses-rbac)
7. [Alur Bisnis Peminjaman, Pengembalian & Perpanjangan](#7-alur-bisnis-peminjaman-pengembalian--perpanjangan)
8. [Notifikasi (Email, In-App, WhatsApp/Fonnte)](#8-notifikasi-email-in-app-whatsappfonnte)
9. [Keamanan Siber (Cybersecurity)](#9-keamanan-siber-cybersecurity)
10. [Enkripsi & Perlindungan Data](#10-enkripsi--perlindungan-data)
11. [Pengujian (Testing)](#11-pengujian-testing)
12. [Environment Variable & Secret Penting](#12-environment-variable--secret-penting)
13. [Lampiran: Dokumentasi Lengkap per Topik](#13-lampiran-dokumentasi-lengkap-per-topik)
    - [13.1 Arsitektur](#131-lampiran--arsitektur-docsarchitecturemd)
    - [13.2 Keamanan Aplikasi Detail](#132-lampiran--keamanan-aplikasi-detail-docssecuritymd)
    - [13.3 Role & Permission Detail](#133-lampiran--role--permission-detail-docspermissionsmd)
    - [13.4 Alur Peminjaman Detail](#134-lampiran--alur-peminjaman-detail-docsloan-flowmd)
    - [13.5 Panduan Instalasi & Deployment](#135-lampiran--panduan-instalasi--deployment-readmemd)

---

## 1. Tentang Aplikasi

**Sistem Informasi Peminjaman Buku** adalah aplikasi manajemen perpustakaan berbasis web yang dibangun dengan Laravel 12. Aplikasi ini dibuat untuk menggantikan proses pencatatan manual (buku besar/Excel) dengan sistem digital yang mengelola siklus hidup lengkap sebuah perpustakaan:

- **Katalog** buku dan eksemplar fisiknya (satu judul buku bisa punya banyak eksemplar/kopi fisik, masing-masing dengan barcode sendiri).
- **Keanggotaan** — data anggota perpustakaan dengan data pribadi sensitif (NIK, alamat, telepon) yang dilindungi enkripsi.
- **Peminjaman** — dari pengajuan anggota, verifikasi petugas, serah-terima fisik dengan bukti foto, sampai pengembalian (juga dengan bukti foto).
- **Perpanjangan peminjaman** (banding) — anggota mengajukan tambahan hari, petugas/admin menyetujui atau menolak.
- **Notifikasi** otomatis lewat email, in-app, dan WhatsApp (Fonnte) untuk setiap kejadian penting (disetujui, ditolak, jatuh tempo, terlambat, dll).
- **Laporan** peminjaman, pengembalian, keterlambatan, dan statistik — bisa diekspor CSV.
- **Landing page publik** dengan SEO yang dapat dikustomisasi Super Admin tanpa menyentuh kode.
- **Keamanan tingkat produksi**: RBAC (role & permission granular), 2FA opsional, enkripsi data sensitif, audit trail, dan berbagai proteksi terhadap serangan web umum (dijelaskan lengkap di [§9](#9-keamanan-siber-cybersecurity) dan [§10](#10-enkripsi--perlindungan-data)).

Target pengguna: instansi perpustakaan (sekolah, kampus, atau umum) dengan beberapa peran staf (admin perpustakaan, petugas sirkulasi, pimpinan yang hanya perlu laporan) dan anggota (siswa/mahasiswa/masyarakat) yang meminjam buku secara mandiri lewat portal sendiri.

---

## 2. Fitur-Fitur Utama

### Master Data
- **Buku** — judul, ISBN, kategori, rak, sampul (upload gambar), status aktif/nonaktif.
- **Eksemplar Buku** — barcode unik per kopi fisik, kondisi (baik/rusak ringan/rusak berat/hilang), status (tersedia/dipinjam/dipesan/perbaikan/hilang).
- **Kategori, Rak** — CRUD standar dengan pencarian, filter, dan pagination.

### Keanggotaan
- Data anggota dengan **field sensitif terenkripsi** (telepon, alamat, NIK — lihat [§10](#10-enkripsi--perlindungan-data)).
- Status anggota (aktif/nonaktif/suspend) yang memengaruhi kelayakan meminjam.
- Anggota bisa punya akun login sendiri (opsional, tertaut ke tabel `users`) untuk mengajukan peminjaman mandiri. Kata sandinya diatur langsung oleh petugas saat menambah atau mengubah anggota — tidak bergantung pada email verifikasi/reset, sehingga anggota bisa langsung masuk.

### Peminjaman, Pengembalian & Perpanjangan
Lihat diagram alur lengkap di [§7](#7-alur-bisnis-peminjaman-pengembalian--perpanjangan). Ringkas: anggota (atau petugas atas nama anggota) mengajukan → petugas menyetujui/menolak → serah-terima fisik disertai bukti foto → pengembalian dicatat dengan memilih eksemplar dan mengunggah bukti foto. Selama masa pinjam, anggota bisa mengajukan **perpanjangan (banding)** yang di-acc atau ditolak admin.

### Notifikasi & Reminder Otomatis
Pengingat pengambilan, jatuh tempo, dan keterlambatan dikirim otomatis lewat scheduler (email + in-app + WhatsApp), lihat [§8](#8-notifikasi-email-in-app-whatsappfonnte).

### Laporan
Laporan peminjaman, pengembalian, keterlambatan, dan statistik ringkasan (grafik per bulan) — dapat difilter dan diekspor CSV.

### Keamanan & Administrasi
- **RBAC** (5 role bawaan, permission granular per aksi) dengan **layar manajemen Role & Permission** langsung dari UI (Super Admin bisa membuat role kustom dan mengatur permission-nya tanpa menyentuh kode) — lihat [§6](#6-role--hak-akses-rbac).
- **2FA (TOTP)** opsional untuk semua pengguna.
- **Audit trail** (log aktivitas) append-only untuk semua aksi sensitif.
- **Security Dashboard** — ringkasan percobaan login gagal, aktivitas 2FA, dan kejadian keamanan lain.
- **Pengaturan Aplikasi** terpusat: identitas instansi, branding (logo, favicon, warna utama, background halaman login), aturan peminjaman & perpanjangan, notifikasi, dan konten landing page — semua bisa diubah Super Admin tanpa deploy ulang.

### Halaman Depan (Landing Page) & SEO
Halaman publik (`/`) yang kontennya (judul hero, gambar, teks "tentang") diatur dari menu Pengaturan, lengkap dengan meta tag SEO, Open Graph, JSON-LD, serta `robots.txt`/`sitemap.xml` yang dihasilkan secara dinamis (bukan file statis) agar selalu mengikuti domain sebenarnya.

---

## 3. Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Backend framework | Laravel 12 (PHP ^8.2, dikembangkan dengan PHP 8.4) |
| Autentikasi & 2FA | Laravel Fortify (`laravel/fortify`) — login, reset password, verifikasi email, TOTP 2FA |
| RBAC (role & permission) | `spatie/laravel-permission` |
| Basis data | MySQL 8.x / MariaDB 10.x (produksi & development), SQLite in-memory (khusus test) |
| Frontend/UI | Blade templates + Tailwind CSS 3.x, tema **Argon Dashboard Tailwind** (Creative Tim), Chart.js untuk grafik, Tom Select untuk dropdown pencarian |
| Build asset | Vite + npm |
| Queue | Database queue driver (`QUEUE_CONNECTION=database`) untuk notifikasi asinkron |
| Testing | PHPUnit, database SQLite in-memory terisolasi dari data development/produksi |
| Code style | Laravel Pint |
| Notifikasi WhatsApp | Gateway pihak ketiga **Fonnte** (REST API) |

---

## 4. Arsitektur & Struktur Kode

### Struktur Folder Inti

```
app/
├── Actions/              Business logic (bukan di controller)
│   └── Loans/             CreateLoan, ApproveLoan, RejectLoan, HandoverLoan,
│                          ReturnLoan, ExpireLoan, CancelLoan,
│                          RequestLoanExtension, ApproveLoanExtension,
│                          RejectLoanExtension
├── Console/Commands/      loans:mark-overdue, loans:expire-approved,
│                          loans:process-reminders (dijalankan scheduler)
├── Enums/                 LoanStatus, ExtensionStatus, BookCopyStatus,
│                          BookCondition, MemberStatus — transisi status
│                          legal didefinisikan di dalam enum itu sendiri
├── Exceptions/            LoanException — pelanggaran aturan bisnis,
│                          ditangkap controller → flash message (bukan error 500)
├── Http/
│   ├── Controllers/       Tipis: validasi via FormRequest, otorisasi via
│   │                      Policy, delegasi ke Action class
│   ├── Middleware/        SecurityHeaders (header keamanan di semua respons)
│   └── Requests/          Satu FormRequest per operasi tulis (validasi terpusat)
├── Models/                Eloquent + relasi; casting encrypted/enum
├── Notifications/         Satu class per jenis notifikasi, channel
│   ├── Channels/           database + mail + WhatsApp (Fonnte), ShouldQueue
│   └── Concerns/
├── Policies/              Satu per model — otorisasi tingkat objek (mencegah IDOR)
└── Support/
    ├── Activity.php        Helper audit log (App\Support\Activity::log())
    ├── BlindIndex.php      HMAC blind-index untuk field terenkripsi
    ├── Color.php            Konversi warna untuk tema dinamis
    └── Concerns/HasUuid.php Trait UUID + route-model-binding
```

### Alur Request Khas

```
Route (middleware permission:xxx, opsional)
  → FormRequest (authorize() via Policy + rules())
    → Controller (tipis — hanya orkestrasi)
      → Action class (DB::transaction, aturan bisnis, row locking)
        → Model
      → Notification (di-queue)
      → Activity::log(...)  (audit trail)
    ← redirect()->with('success'|'error', ...)
```

Controller **tidak pernah** memakai `$request->all()` untuk membuat/mengubah model — selalu lewat `$request->validated()` dari FormRequest. Ini mencegah *mass assignment* field sensitif (lihat [§9](#9-keamanan-siber-cybersecurity)).

### Keputusan Desain Penting

| Keputusan | Alasan |
|---|---|
| UUID publik (bukan `id` auto-increment) di semua URL | ID berurutan mudah ditebak/di-enumerasi; UUID hanya identifier, **tetap wajib lewat Policy** untuk otorisasi. |
| Reservasi eksemplar terjadi saat **disetujui**, bukan saat **diajukan** | Mengunci baris database hanya saat benar-benar dialokasikan, bukan menahan lock sepanjang masa menunggu. |
| Kode peminjaman (`PJ-YYYYMMDD-000001`) via tabel sequence + row locking | Aman dari race condition saat dua peminjaman dibuat bersamaan, portable MySQL ↔ SQLite (dipakai test). |
| Reminder idempotent (tabel `loan_reminders`, unique constraint) | Command terjadwal aman dijalankan berulang tanpa mengirim notifikasi duplikat. |
| Ekspor laporan CSV native (bukan library spreadsheet) | Tidak menambah dependency tanpa kebutuhan jelas. |
| Test pakai SQLite in-memory | Database development/produksi tidak pernah tersentuh oleh test suite. |

---

## 5. Struktur Basis Data

Tabel utama (di luar tabel bawaan Laravel/Fortify/Spatie seperti `users`, `password_reset_tokens`, `roles`, `permissions`):

| Tabel | Fungsi |
|---|---|
| `settings` | Key-value pengaturan aplikasi (di-cache, lihat `App\Models\Setting`) |
| `activity_logs` | Audit trail append-only |
| `categories`, `racks` | Master data pendukung buku |
| `books` | Judul buku (kategori + rak; modul penulis/penerbit sudah dihapus) |
| `book_copies` | Eksemplar fisik per buku, barcode unik, status & kondisi |
| `members` | Data anggota — **berisi field terenkripsi**, lihat [§10](#10-enkripsi--perlindungan-data) |
| `loan_number_sequences` | Counter harian untuk pembuatan kode peminjaman (aman dari race condition) |
| `loans` | Header peminjaman (status, tanggal ajuan/setuju/ambil/jatuh-tempo/kembali) |
| `loan_items` | Detail per buku dalam satu peminjaman, tertaut ke `book_copies`, berisi path bukti foto serah terima & pengembalian |
| `loan_extensions` | Pengajuan perpanjangan/banding beserta keputusan admin dan pergeseran jatuh tempo |
| `loan_reminders` | Log reminder terkirim (mencegah duplikat) |
| `notifications` | Notifikasi in-app (bawaan Laravel Notifications) |

---

## 6. Role & Hak Akses (RBAC)

Otorisasi **selalu** lewat pemeriksaan permission (`$user->can('...')`) atau Policy — **tidak pernah** `if ($user->role === '...')` yang rapuh dan sulit diaudit.

| Role | Ringkasan Akses |
|---|---|
| `super-admin` | Seluruh permission. Satu-satunya yang bisa mengelola User, Role & Permission, Pengaturan Aplikasi (termasuk keamanan), dan melihat Audit Log/Security Dashboard penuh. |
| `admin-perpustakaan` | Kelola penuh master data (buku, eksemplar, kategori, rak), anggota beserta akun loginnya, seluruh transaksi peminjaman/pengembalian/perpanjangan, dan laporan. |
| `petugas` | Lihat master data, ajukan peminjaman atas nama anggota, verifikasi peminjaman, serah-terima (handover), proses pengembalian, putuskan perpanjangan. Tidak bisa mengubah master data. |
| `anggota` | Lihat katalog, ajukan/batalkan peminjaman sendiri, ajukan perpanjangan, lihat riwayatnya sendiri saja. |
| `pimpinan` | Akses baca-saja — hanya dashboard & laporan. |

Permission diatur granular per aksi (contoh: `books.view`, `books.create`, `loans.approve`, `loan-extensions.approve`, dll — daftar lengkap di [docs/permissions.md](permissions.md)). Sejak fitur **manajemen Role & Permission via UI**, Super Admin bisa:
- Membuat role kustom baru dan mencentang permission mana saja yang dimiliki role tersebut.
- Melihat referensi permission (siapa saja yang punya permission apa) di halaman Permission.
- Role bawaan sistem (`super-admin`, `admin-perpustakaan`, `petugas`, `anggota`, `pimpinan`) **tidak bisa dihapus atau di-rename**, dan permission `super-admin` **tidak bisa diubah** — dua-duanya diproteksi di level kode (`RoleController`), bukan hanya di UI, supaya sistem tidak bisa "dikunci sendiri" oleh kesalahan konfigurasi.

### Object-Level Authorization (mencegah IDOR)

Permission saja tidak cukup untuk mencegah satu anggota melihat data anggota lain. Setiap model yang punya "pemilik" (`Loan`, `LoanExtension`, `Member`) memakai Policy dengan pola:

```php
public function view(User $user, Loan $loan): bool
{
    if ($user->can('loans.view-all')) {
        return true;
    }

    return $loan->member->user_id === $user->id;
}
```

UUID acak pada resource yang tidak ada menghasilkan **404**, UUID milik orang lain menghasilkan **403** — dua respons yang berbeda secara sengaja supaya penyerang tidak bisa membedakan "tidak ada" dari "ada tapi bukan milik Anda" lewat status code semata... (detail lebih lanjut lihat [§9](#9-keamanan-siber-cybersecurity)).

---

## 7. Alur Bisnis Peminjaman, Pengembalian & Perpanjangan

```
Anggota mengajukan
        │
        ▼
     PENDING ─────────────────┐
        │                     │ ditolak (alasan wajib diisi)
        │ disetujui            ▼
        │ (kunci & pesan     REJECTED (selesai)
        │  1 eksemplar/item)
        ▼
     APPROVED ──── tidak diambil sebelum batas waktu ────┐
        │                                                 │ (otomatis via scheduler)
        │ serah-terima + bukti foto                      ▼
        ▼                                              EXPIRED (selesai,
     BORROWED ──── melewati jatuh tempo ────┐            eksemplar dilepas)
        │   ▲                               │ (otomatis via scheduler)
        │   │ perpanjangan di-acc admin     ▼
        │   └─────────────────────────── OVERDUE
        │ pilih eksemplar + bukti foto      │
        │◄───── pilih eksemplar + bukti foto ┘
        ▼
     RETURNED (selesai)

PENDING/APPROVED juga bisa dibatalkan anggota (sebelum diproses)
atau petugas/admin.
```

**Titik kunci konkurensi**: saat menyetujui peminjaman, sistem mengunci baris eksemplar yang berstatus tersedia (`lockForUpdate()`) sehingga **dua persetujuan bersamaan untuk buku yang sama tidak akan pernah mengambil eksemplar fisik yang sama** — diuji otomatis di test suite.

**Perpanjangan / banding**: selama peminjaman berstatus BORROWED atau OVERDUE, anggota dapat mengajukan tambahan hari beserta alasannya dari halaman detail peminjaman. Petugas/admin memutuskan dari menu **Transaksi > Perpanjangan**:
```
base       = jatuh tempo lama bila masih di masa depan, selain itu hari ini
jatuh tempo baru = base + jumlah hari yang disetujui
```
Jatuh tempo peminjaman **dan** seluruh eksemplar yang belum kembali ikut digeser; peminjaman yang tadinya OVERDUE kembali menjadi BORROWED. Penolakan tidak mengubah jatuh tempo dan wajib menyertakan alasan yang dikirimkan ke anggota. Batas berapa kali satu peminjaman boleh diperpanjang (`max_renewals`) dan saklar fiturnya (`allow_renewal`) dibaca dari **Pengaturan Aplikasi**, tidak pernah di-hardcode.

**Bukti foto**: serah terima buku ke anggota bisa disertai foto (bisa juga diunggah menyusul dari halaman Peminjaman Aktif), sedangkan pengembalian **wajib** disertai foto — petugas memilih eksemplar dari daftar peminjaman aktif, tidak ada lagi input/scan barcode.

Penjelasan lengkap (termasuk kondisi eksemplar saat kembali dan jadwal reminder) ada di [docs/loan-flow.md](loan-flow.md).

---

## 8. Notifikasi (Email, In-App, WhatsApp/Fonnte)

Setiap kejadian penting pada peminjaman/perpanjangan mengirim notifikasi lewat kombinasi channel berikut, tergantung Pengaturan Aplikasi:

| Channel | Kapan aktif |
|---|---|
| **Database (in-app)** | Selalu aktif — muncul di ikon lonceng navbar. |
| **Email** | Aktif jika toggle "Aktifkan notifikasi email" dinyalakan (default: nyala). |
| **WhatsApp (Fonnte)** | Aktif jika toggle "Aktifkan notifikasi WhatsApp" dinyalakan **dan** `FONNTE_TOKEN` sudah diisi di server (`.env`). |

Jenis notifikasi yang dikirim: pengajuan terkirim, ada pengajuan baru (ke petugas), disetujui, ditolak, pengingat pengambilan, pengingat jatuh tempo, terlambat, pengembalian berhasil, pengajuan perpanjangan terkirim, ada pengajuan perpanjangan baru (ke petugas), perpanjangan disetujui, perpanjangan ditolak.

### Cara Kerja Integrasi Fonnte

1. Setiap notifikasi yang relevan memakai trait `SendsLibraryNotifications` yang menentukan channel mana saja yang aktif (`database`, `mail`, dan/atau kelas `FonnteChannel`) berdasarkan Pengaturan Aplikasi.
2. `FonnteChannel` (`app/Notifications/Channels/FonnteChannel.php`) mengirim `POST` ke endpoint Fonnte (`config('services.fonnte.url')`, default `https://api.fonnte.com/send`) dengan header `Authorization: <FONNTE_TOKEN>`, membawa nomor tujuan dan teks pesan.
3. Nomor WhatsApp tujuan diambil dari nomor telepon anggota (`Member.phone`, yang **tersimpan terenkripsi** — lihat [§10](#10-enkripsi--perlindungan-data)) lewat `User::routeNotificationForFonnte()`, dan dinormalisasi otomatis ke format `62xxxxxxxxxx` (mis. `08123456789` → `628123456789`).
4. Isi pesan WhatsApp memakai ulang judul & teks yang sama dengan notifikasi in-app (`toDatabase()`), jadi tidak perlu menulis teks tiga kali untuk tiga channel berbeda.
5. **Kegagalan pengiriman WhatsApp tidak pernah mengganggu channel lain.** Jika Fonnte sedang down/error/timeout, kesalahan hanya dicatat ke log aplikasi (`storage/logs/laravel.log`) — notifikasi email dan in-app tetap terkirim seperti biasa. Ini penting karena WhatsApp adalah layanan pihak ketiga eksternal yang berada di luar kendali aplikasi.
6. Jika `FONNTE_TOKEN` kosong atau anggota tidak punya nomor telepon, pengiriman WA otomatis dilewati (tidak ada error, tidak ada percobaan HTTP).

Dua variabel yang mengatur fitur ini **sengaja dipisah tanggung jawabnya**:
- `whatsapp_notification_enabled` (di menu **Pengaturan Aplikasi**) — mengatur apakah fitur ini **dipakai secara operasional** oleh instansi.
- `FONNTE_TOKEN` (di `.env`, server-side) — mengatur apakah sistem **secara teknis mampu** mengirim (kredensial API). Token ini tidak pernah ditaruh di database/Pengaturan karena termasuk secret yang setara dengan password (lihat [§10](#10-enkripsi--perlindungan-data)).

---

## 9. Keamanan Siber (Cybersecurity)

Baseline yang diikuti setara **OWASP ASVS Level 2** — setiap poin di bawah ini adalah implementasi nyata di kode (bisa diverifikasi langsung, bukan sekadar dokumen niat), dan sebagian besar dijaga oleh automated test.

### 9.1 Autentikasi

- Login, logout, reset password, verifikasi email, dan 2FA ditangani **Laravel Fortify** — implementasi teruji milik framework, bukan racikan sendiri yang rawan bug.
- **Registrasi publik dinonaktifkan** — akun hanya bisa dibuat oleh admin lewat menu internal. Ini mencegah orang tak dikenal membuat akun sendiri (default aman untuk sistem internal instansi).
- **Password** selalu di-*hash* dengan `bcrypt` (cast `'password' => 'hashed'` di model `User`) — tidak pernah dibandingkan atau disimpan dalam bentuk plaintext, dan tidak bisa "dibalikkan" ke teks asli meskipun database bocor.
- **2FA (TOTP — Time-based One-Time Password)**, kompatibel dengan Google Authenticator/Authy dkk., bersifat **opsional untuk semua role** (bisa diaktifkan sendiri lewat halaman Profil).
- Setiap percobaan login membatasi **5 percobaan per menit** berdasarkan kombinasi email+IP (mencegah *brute-force* tebak password), tanpa mengunci akun secara permanen (mencegah limiter ini disalahgunakan orang lain untuk mem-DoS akun korban).

### 9.2 Otorisasi & Broken Access Control (termasuk IDOR)

*Broken Access Control* adalah risiko #1 di OWASP Top 10 — aplikasi ini menanganinya berlapis:

1. **Lapis rute**: middleware `permission:nama.permission` di setiap route sensitif — pengguna tanpa permission langsung mendapat 403 sebelum kode controller sempat berjalan.
2. **Lapis objek (Policy)**: bahkan jika seseorang punya permission "lihat peminjaman", Policy memastikan dia hanya bisa melihat peminjaman **miliknya sendiri** kecuali dia juga punya permission "lihat semua". Ini yang disebut **IDOR (Insecure Direct Object Reference)** — kerentanan klasik di mana pengguna bisa mengubah ID di URL untuk mengakses data orang lain.
3. **UUID di URL publik** (bukan angka berurutan seperti `/loans/1`, `/loans/2`) membuat ID sulit ditebak, **tapi ini bukan pengganti Policy** — UUID acak yang tidak ada tetap menghasilkan 404, dan UUID milik orang lain tetap menghasilkan 403 dari Policy, bukan lolos begitu saja.
4. Tidak ada jalan pintas ("bypass") global untuk `super-admin` di kode otorisasi — super-admin bisa mengakses segalanya karena permission-nya memang di-*seed* lengkap secara eksplisit, bukan lewat celah yang melewati Policy.

### 9.3 Injection (SQL Injection)

- Seluruh akses database lewat Eloquent ORM atau Query Builder dengan **parameter binding otomatis** — tidak pernah menggabungkan string input pengguna langsung ke dalam query SQL.
- Beberapa query mentah (`DB::statement`/`selectRaw`, dipakai untuk fitur spesifik-database seperti statistik bulanan dan penomoran kode peminjaman) hanya berisi ekspresi SQL tetap per jenis database, **tidak pernah** menyisipkan data dari request pengguna ke dalamnya.

### 9.4 Cross-Site Scripting (XSS)

- Semua output ke halaman lewat sintaks Blade `{{ }}` yang **otomatis meng-escape HTML** — input pengguna (nama, judul buku, catatan, dll) tidak pernah bisa dieksekusi sebagai kode HTML/JavaScript di browser pengguna lain.
- Tidak ada satupun pemakaian `{!! !!}` (output mentah tanpa escape) untuk konten yang berasal dari input pengguna di seluruh aplikasi.
- **Content-Security-Policy (CSP)** aktif di setiap respons (lihat §9.7) sebagai lapis pertahanan tambahan — bahkan jika XSS lolos karena bug tak terduga, CSP membatasi script mana yang browser mau jalankan.

### 9.5 Cross-Site Request Forgery (CSRF)

- Middleware CSRF Laravel (`VerifyCsrfToken`) aktif otomatis di **semua** route yang mengubah data (POST/PUT/PATCH/DELETE) — setiap form menyertakan token tersembunyi yang divalidasi server sebelum aksi dijalankan. Tanpa token yang valid, permintaan ditolak (HTTP 419).

### 9.6 Mass Assignment

- Tidak ada satupun controller yang membuat/mengubah model langsung dari `$request->all()`. Semua input divalidasi lebih dulu lewat **FormRequest** khusus per aksi, dan hanya field yang lolos validasi (`$request->validated()`) yang dipakai.
- Field sensitif seperti `role`, status persetujuan, atau permission **tidak pernah** bisa diisi lewat field form biasa — field ini hanya diset lewat pemanggilan method eksplisit di controller (misalnya `$role->syncPermissions([...])`), sehingga pengguna tidak bisa "menyelundupkan" field ekstra di body request untuk menaikkan hak aksesnya sendiri.

### 9.7 Rate Limiting & Perlindungan Brute-Force

| Endpoint | Batas |
|---|---|
| Login | 5 kali/menit per kombinasi email+IP |
| Verifikasi 2FA | 5 kali/menit per sesi login |
| Lupa/reset password & verifikasi email | 10 kali/menit per IP |
| Pencarian barcode eksemplar | 60 kali/menit per pengguna |
| Upload file (avatar, sampul buku, branding) | 20 kali/menit per pengguna |

### 9.8 Security Headers

Middleware global `SecurityHeaders` menambahkan header berikut ke **setiap** respons HTTP:

- `Content-Security-Policy` — membatasi dari domain mana browser boleh memuat script/style/font/gambar, termasuk `frame-ancestors 'self'` untuk mencegah **clickjacking** (situs lain menaruh aplikasi ini di dalam `<iframe>` tersembunyi).
- `X-Content-Type-Options: nosniff` — mencegah browser "menebak-nebak" tipe file yang bisa dieksploitasi.
- `Referrer-Policy: strict-origin-when-cross-origin` — membatasi informasi URL yang bocor ke situs lain lewat header Referer.
- `Permissions-Policy` — menonaktifkan akses kamera/mikrofon/lokasi yang tidak dibutuhkan aplikasi ini sama sekali.
- `Strict-Transport-Security` (HSTS) — otomatis aktif saat diakses lewat HTTPS, memaksa browser selalu memakai HTTPS untuk kunjungan berikutnya.

### 9.9 Keamanan Sesi

- Cookie sesi bertanda **`HttpOnly`** (tidak bisa dibaca JavaScript, mengurangi dampak XSS) dan **`SameSite=Lax`** (mengurangi risiko CSRF lintas situs).
- `SESSION_SECURE_COOKIE` wajib diaktifkan manual di produksi (`.env`) saat aplikasi sudah berjalan di HTTPS, agar cookie sesi hanya dikirim lewat koneksi terenkripsi.
- Sesi diregenerasi otomatis setiap kali login berhasil (mencegah **session fixation**) dan langsung tidak berlaku saat logout.
- Mengganti password otomatis membatalkan sesi aktif lain di perangkat lain (`AuthenticateSession` middleware).
- Pengguna bisa "Keluar dari semua perangkat lain" sendiri lewat halaman Profil.

### 9.10 Upload File

- Tipe file diverifikasi dari **isi file** (magic bytes), bukan dari ekstensi nama file yang dikirim klien (nama file bisa dipalsukan dengan mudah).
- Ukuran file dibatasi per jenis (sampul buku, avatar, logo, dll).
- **Nama file diacak otomatis** oleh Laravel saat disimpan — nama asli dari komputer pengguna tidak pernah dipercaya/dipakai sebagai path di server.
- **SVG tidak diizinkan** di manapun (sampul buku, avatar, branding) — file SVG bisa menyisipkan JavaScript berbahaya (`<script>` di dalam file "gambar").

### 9.11 Audit Trail (Jejak Aktivitas)

- Tabel `activity_logs` bersifat **append-only** — tidak ada tombol edit atau hapus untuk log ini di manapun di aplikasi, sehingga log tidak bisa dimanipulasi untuk menutupi jejak.
- Fungsi pencatat log (`Activity::log()`) **tidak pernah** dipanggil dengan menyertakan password, kode OTP, secret 2FA, kode pemulihan, atau token — hanya deskripsi teks dan perubahan data yang tidak sensitif (misalnya "role diubah dari A ke B", bukan isi password baru).
- Bisa dilihat lewat menu Administrasi > Audit Log, dan diringkas otomatis di Security Dashboard (percobaan login gagal hari ini, kegagalan 2FA, kejadian keamanan lain).

### 9.12 Penanganan Error

- Di produksi, `APP_DEBUG` **wajib** `false` — pengguna tidak pernah melihat stack trace, path server, query SQL, atau kredensial apapun saat terjadi error. Yang tampil hanya halaman error generik.

### 9.13 Konfirmasi Ulang Password untuk Aksi Sensitif

Aksi berisiko tinggi berikut mewajibkan pengguna memasukkan ulang passwordnya (meski sudah login) sebelum diproses: mengubah role/permission pengguna lain, menonaktifkan akun, membuat ulang kode pemulihan 2FA, menonaktifkan 2FA, dan mengubah Pengaturan Aplikasi. Ini mencegah aksi sensitif dilakukan lewat sesi yang "dicuri" sesaat (misalnya laptop yang ditinggal dalam keadaan login).

---

## 10. Enkripsi & Perlindungan Data

Tiga *secret* berbeda dipakai di aplikasi ini dan **sengaja tidak pernah disamakan atau saling menggantikan**:

| Secret | Fungsi | Lokasi |
|---|---|---|
| `APP_KEY` | Kunci enkripsi utama Laravel (AES-256) — dipakai untuk semua cast `encrypted` dan enkripsi sesi | `.env` |
| `BLIND_INDEX_KEY` | Kunci HMAC terpisah, khusus untuk pencarian pada data terenkripsi (lihat §10.3) | `.env` |
| `DB_PASSWORD` | Kredensial akses database | `.env` |

Ketiganya **berbeda per environment** (development ≠ staging ≠ produksi) dan tidak boleh dipakai ulang untuk keperluan lain.

### 10.1 Ringkasan Perlakuan Setiap Data Sensitif

| Data | Perlakuan | Algoritma/Kunci |
|---|---|---|
| Password akun (`users.password`) | **Hash satu-arah**, tidak bisa dibalikkan sama sekali | `bcrypt` |
| Kode rahasia 2FA (`two_factor_secret`) & kode pemulihan (`two_factor_recovery_codes`) | **Enkripsi dua-arah** (bisa didekripsi saat dibutuhkan sistem) — dilakukan otomatis oleh Laravel Fortify | AES-256-CBC via `APP_KEY` (Laravel `Crypt`) |
| Telepon anggota (`members.phone`) | Enkripsi dua-arah | AES-256-CBC via `APP_KEY` (Eloquent cast `encrypted`) |
| Alamat anggota (`members.address`) | Enkripsi dua-arah | AES-256-CBC via `APP_KEY` (Eloquent cast `encrypted`) |
| NIK/nomor identitas anggota (`members.identity_number`) | Enkripsi dua-arah **+** indeks pencarian terpisah | AES-256-CBC via `APP_KEY`, ditambah blind index HMAC-SHA256 via `BLIND_INDEX_KEY` (kolom `identity_number_index`) |
| Email (users/members) | **Tidak dienkripsi** (disengaja) | — |

### 10.2 Kenapa Password dan 2FA Diperlakukan Berbeda?

- **Password**: sistem **tidak pernah** perlu tahu password asli pengguna — sistem hanya perlu memverifikasi "apakah yang diketik cocok". Karena itu password memakai **hashing satu-arah** (`bcrypt`), bukan enkripsi. Bahkan jika database bocor total, password asli tidak bisa dipulihkan (hanya bisa ditebak lewat brute-force yang sangat mahal secara komputasi berkat `bcrypt`).
- **Kode rahasia 2FA**: berbeda dengan password, sistem **harus** bisa membaca kembali kode rahasia ini setiap kali pengguna login untuk menghitung ulang kode OTP 6 digit yang seharusnya muncul di aplikasi Authenticator pengguna, lalu membandingkannya. Karena itu field ini memakai **enkripsi dua-arah** (bukan hash), yang sudah ditangani otomatis oleh Fortify — aplikasi ini **sengaja tidak menambahkan lapisan enkripsi Eloquent lagi di atasnya**, karena akan menyebabkan data terenkripsi dua kali (double-encryption) dan berisiko rusak.

### 10.3 Kenapa NIK Butuh "Blind Index" Terpisah, Bukan Cukup Dienkripsi Saja?

Ini bagian yang paling sering disalahpahami, jadi dijelaskan detail:

- NIK harus **dienkripsi** karena merupakan data pribadi sensitif (bisa dipakai untuk pencurian identitas jika bocor).
- Tapi sistem tetap perlu memastikan **tidak ada dua anggota dengan NIK yang sama** (validasi unik) — dan validasi unik/pencarian **tidak bisa dilakukan langsung di atas data terenkripsi**, karena hasil enkripsi AES selalu berbeda setiap kali dienkripsi ulang (bahkan untuk input yang sama persis), jadi database tidak bisa membandingkan `WHERE identity_number = '...'`.
- Solusinya: selain kolom `identity_number` (terenkripsi, untuk ditampilkan kembali ke petugas yang berwenang), disimpan juga kolom kedua `identity_number_index` — hasil **HMAC-SHA256** dari NIK tersebut memakai kunci rahasia `BLIND_INDEX_KEY`. HMAC dengan kunci yang **sama akan selalu menghasilkan output yang sama**, sehingga kolom ini bisa diberi *unique constraint* di database dan dicari dengan cepat (`WHERE identity_number_index = hash(...)`) — tanpa harus mendekripsi semua baris satu per satu.
- Kenapa harus **HMAC berkunci**, bukan `SHA-256` biasa? Karena NIK di Indonesia formatnya sangat terstruktur dan jumlah kemungkinannya terbatas — jika dipakai hash biasa tanpa kunci rahasia, seseorang yang berhasil mencuri database (tanpa perlu mencuri `APP_KEY` sekalipun) bisa **membuat tabel hash semua kemungkinan NIK** (rainbow table) dan mencocokkannya satu per satu untuk membongkar NIK asli. Dengan HMAC + kunci rahasia terpisah (`BLIND_INDEX_KEY`, berbeda dari `APP_KEY`), penyerang harus mencuri **dua secret berbeda sekaligus** (bukan cukup satu) untuk bisa membongkar data ini — memperbesar usaha yang dibutuhkan secara signifikan.

### 10.4 Kenapa Email Tidak Dienkripsi?

Email dipakai sebagai **kunci login** (harus bisa dicari cepat `WHERE email = '...'` oleh Fortify) dan harus **unik** di seluruh sistem. Menerapkan pola enkripsi+blind-index yang sama seperti NIK ke email akan menambah kompleksitas besar (mengubah cara kerja login bawaan Fortify) tanpa manfaat keamanan yang sepadan — email umumnya bukan data serahasia NIK/alamat/telepon dan sering sudah diketahui pihak lain (untuk komunikasi resmi, dsb).

### 10.5 Enkripsi Data "Dalam Perjalanan" (In-Transit)

Enkripsi di atas melindungi data **saat tersimpan** (*at rest*) di database. Untuk melindungi data **saat dikirim** antara browser pengguna dan server (*in transit*), aplikasi bergantung pada **HTTPS/TLS** yang wajib diaktifkan di server produksi (lihat checklist deployment di `README.md`) — ini di luar kode aplikasi itu sendiri dan menjadi tanggung jawab konfigurasi server (reverse proxy Nginx/Apache dengan sertifikat TLS).

---

## 11. Pengujian (Testing)

Semua kontrol keamanan dan alur bisnis di atas **diuji otomatis**, bukan hanya diklaim di dokumen. Test dijalankan dengan:

```bash
php artisan test
```

Test memakai database **SQLite in-memory** (dikonfigurasi khusus untuk lingkungan test) sehingga **database development/produksi tidak pernah tersentuh** oleh proses testing.

Cakupan pengujian mencakup: autentikasi & 2FA, batasan RBAC per role, IDOR (anggota tidak bisa melihat data anggota lain), alur peminjaman penuh (termasuk mencegah dua persetujuan mengambil eksemplar fisik yang sama secara bersamaan), perpanjangan peminjaman, bukti foto serah terima & pengembalian, pembuatan akun login anggota (termasuk anggota benar-benar bisa login), render seluruh halaman, manajemen Role & Permission (termasuk proteksi role bawaan), notifikasi WhatsApp/Fonnte (terkirim/tidak terkirim sesuai kondisi), pengaturan aplikasi, serta kontrol keamanan (CSRF, mass assignment, validasi upload file, anggota nonaktif diblokir meminjam).

---

## 12. Environment Variable & Secret Penting

Selain variabel standar Laravel, beberapa key khusus aplikasi ini (lihat `.env.example` untuk daftar lengkap dengan komentar):

| Variabel | Fungsi |
|---|---|
| `APP_KEY` | Kunci enkripsi utama (dibuat otomatis oleh `php artisan key:generate`) |
| `BLIND_INDEX_KEY` | Kunci HMAC terpisah untuk blind-index NIK (lihat §10.3) — wajib diisi, dibuat lewat `php artisan tinker --execute="echo base64_encode(random_bytes(32));"` |
| `DB_*` | Koneksi database MySQL/MariaDB |
| `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` | Dipakai satu kali oleh seeder untuk membuat akun super-admin pertama |
| `FONNTE_TOKEN` | Token API gateway WhatsApp Fonnte — kosongkan untuk menonaktifkan pengiriman WhatsApp meski toggle di Pengaturan dinyalakan |
| `FONNTE_API_URL` | Endpoint API Fonnte (default `https://api.fonnte.com/send`, biasanya tidak perlu diubah) |
| `SESSION_SECURE_COOKIE` | Wajib diisi `true` saat produksi sudah berjalan di HTTPS |

**Tidak pernah** commit file `.env` ke Git (sudah masuk `.gitignore`). Semua secret di atas harus berbeda nilainya antar environment.

---

## 13. Lampiran: Dokumentasi Lengkap per Topik

Bagian 1–12 di atas adalah rangkuman. Untuk referensi, seluruh isi dokumen sumber (`docs/architecture.md`, `docs/security.md`, `docs/permissions.md`, `docs/loan-flow.md`, `README.md`) disalin utuh di bawah ini juga, supaya file ini benar-benar berdiri sendiri tanpa perlu membuka file lain.

### 13.1 Lampiran — Arsitektur (docs/architecture.md)

#### Struktur Domain

```
app/
├── Actions/            Business logic (spec §48) — controller tetap tipis
│   └── Loans/           CreateLoan, ApproveLoan, RejectLoan, HandoverLoan,
│                        ReturnLoan, ExpireLoan, CancelLoan,
│                        RequestLoanExtension, ApproveLoanExtension,
│                        RejectLoanExtension
├── Console/Commands/    loans:mark-overdue, loans:expire-approved,
│                        loans:process-reminders (scheduler, spec §20)
├── Enums/               LoanStatus, ExtensionStatus, BookCopyStatus,
│                        BookCondition, MemberStatus — transisi status
│                        legal didefinisikan di dalam enum itu sendiri
├── Exceptions/          LoanException — pelanggaran business rule,
│                        ditangkap controller dan ditampilkan sebagai
│                        flash message, bukan 500
├── Http/
│   ├── Controllers/     Tipis: validasi via FormRequest, otorisasi via
│   │                    Policy, delegasi ke Action class
│   ├── Middleware/       EnsureTwoFactorEnabled,
│   │                    PreventDisablingRequiredTwoFactor, SecurityHeaders
│   └── Requests/         Satu FormRequest per operasi tulis
├── Models/               Eloquent + relasi; casting encrypted/enum
├── Notifications/        Satu class per jenis notifikasi (spec §51),
│                        channel database + mail, ShouldQueue
├── Policies/             Satu per model — object-level authorization
│                        (spec §26/§50), termasuk guard IDOR
└── Support/
    ├── Activity.php       Helper audit log (App\Support\Activity::log())
    ├── BlindIndex.php     HMAC blind-index untuk field terenkripsi
    └── Concerns/HasUuid.php  Trait UUID + route-model-binding
```

> Catatan: `EnsureTwoFactorEnabled` dan `PreventDisablingRequiredTwoFactor` disebut di diagram folder asli tapi sudah **dihapus** dari kode saat ini karena 2FA diputuskan bersifat opsional untuk semua role (lihat §9.1). `app/Http/Middleware/` saat ini hanya berisi `SecurityHeaders`. Diagram di atas dipertahankan apa adanya seperti di `docs/architecture.md` sumbernya; lihat §4 untuk struktur folder yang sudah dimutakhirkan.

#### Alur Request Khas

```
Route (permission: middleware, opsional)
  → FormRequest (authorize() via Policy + rules())
    → Controller (tipis — hanya orkestrasi)
      → Action class (DB::transaction, business rules, locking)
        → Model
      → Notification (queued)
      → Activity::log(...)
    ← redirect()->with('success'|'error', ...)
```

Controller **tidak pernah** memanggil `$request->all()` untuk membuat/mengubah model — selalu lewat `$request->validated()`/`$request->safe()` dari FormRequest (spec §30/§69).

#### Keputusan Desain Penting

| Keputusan | Alasan |
|---|---|
| Laravel 12 (bukan "13" seperti disebut di brief awal) | Versi yang benar-benar terpasang; tidak ada rilis Laravel 13 untuk dipasang. |
| `spatie/laravel-permission` untuk RBAC | Diminta eksplisit di spec §2 dan dikonfirmasi user. |
| UUID via trait `HasUuid`, bukan kolom `id` di URL | Spec §7 — UUID hanya identifier publik, Policy tetap wajib. |
| Email anggota/user **tidak** dienkripsi | Pola blind-index di spec §11 eksplisit bersyarat ("bila email ikut dienkripsi"); mengenkripsinya akan merusak lookup login Fortify tanpa manfaat keamanan berarti di sini. |
| `two_factor_secret`/`two_factor_recovery_codes` **tidak** diberi cast `encrypted` tambahan | Fortify's `TwoFactorAuthenticatable` sudah mengenkripsi/dekripsi kedua kolom ini sendiri lewat `Fortify::currentEncrypter()` (default: `Crypt`, berbasis `APP_KEY`). Menambah cast Eloquent di atasnya akan meng-enkripsi ganda dan berisiko rusak di jalur yang tidak konsisten. |
| NIK anggota (`identity_number`) dienkripsi + blind index (`identity_number_index`) | Konkretisasi pola blind-index spec §11 untuk field yang benar-benar butuh pengecekan duplikat. |
| Loan code (`PJ-YYYYMMDD-000001`) via tabel `loan_number_sequences` + `lockForUpdate()` | Aman dari race condition (spec §13/§40), portable MySQL ↔ SQLite (dipakai test). |
| Reservasi eksemplar terjadi saat **approve**, bukan saat **submit** | Spec §14 — mengunci baris hanya saat benar-benar dialokasikan, bukan menahan lock sepanjang masa pending. |
| Reminder idempotent via tabel `loan_reminders` (unique `loan_id+type+milestone`) | Spec §19/§20 — command scheduler aman dijalankan berulang tanpa reminder ganda. |
| Export laporan sebagai CSV native (`streamDownload`), bukan package spreadsheet | Spec §2 — jangan menambah dependency tanpa alasan jelas; CSV cukup untuk kebutuhan ini. |
| PHPUnit (bukan Pest) | Sudah terpasang di skeleton awal proyek. |
| Test pakai SQLite in-memory | Spec §53 — strategi testing tidak boleh menyentuh database development/production. |

#### Katalog vs Master Data "Buku"

Ada dua rute berbeda untuk buku yang sengaja dipisah:

- `CatalogController` (`/catalog`) — tampilan baca-saja untuk **semua** pengguna terautentikasi (spec §41 "Semua User > Katalog"), tidak melalui `BookPolicy`.
- `BookController` (`/books`, Master Data) — layar kelola admin, digerbangi `BookPolicy` + permission `books.*`.

Pemisahan ini mencegah permission `books.view` (untuk admin) tercampur dengan hak akses baca katalog dasar yang semua orang punya.

### 13.2 Lampiran — Keamanan Aplikasi Detail (docs/security.md)

Baseline: OWASP ASVS Level 2 (spec §57). Setiap kontrol di bawah ini adalah implementasi nyata di kode, bukan sekadar checklist.

#### Broken Access Control / IDOR

- Setiap route sensitif digerbangi **middleware permission** (lapis pertama) **dan Policy** (lapis kedua, object-level) — lihat `app/Policies/*`.
- Pola IDOR eksplisit di `LoanPolicy`, `LoanExtensionPolicy`, `MemberPolicy`: `$user->can('...view-all')` atau `$resource->member->user_id === $user->id`.
- UUID (`App\Support\Concerns\HasUuid`) dipakai di semua URL publik, **tapi bukan pengganti otorisasi** — resource acak menghasilkan 404 (tidak ditemukan lewat route-model-binding), resource milik orang lain menghasilkan 403 (ditolak Policy). Dites di `tests/Feature/IdorTest.php`.
- Tidak ada Gate::before bypass global untuk super-admin — super-admin lolos karena permission-nya memang lengkap (di-seed eksplisit), bukan lewat jalan pintas yang melewati Policy (lihat `AppServiceProvider::boot()`).

#### Autentikasi

- Laravel Fortify: login, logout, reset password, verifikasi email, konfirmasi password, 2FA (TOTP).
- Registrasi publik **dinonaktifkan** (`config/fortify.php` — fitur `registration()` sengaja tidak diaktifkan) — default aman spec §58.
- 2FA (TOTP) bersifat opsional untuk semua role — keputusan produk (bukan default spec §6, yang aslinya mewajibkan 2FA untuk staf). Pengguna dapat mengaktifkan/menonaktifkannya sendiri kapan saja lewat halaman Profil; tidak ada middleware yang memblokir akses karena 2FA belum aktif.
- Password di-hash `bcrypt` (`'password' => 'hashed'` cast) — tidak pernah disimpan/dibandingkan plaintext.
- Aksi sensitif (ubah role, disable user, regenerate recovery code, disable 2FA, ubah Pengaturan) memerlukan `password.confirm` — middleware ini digerbangi di rute **GET (tampilkan form) dan POST/PUT** sekaligus, supaya konfirmasi terjadi sebelum form diisi (menghindari data hilang akibat redirect konfirmasi yang tidak mempertahankan body POST).

#### Enkripsi Data (spec §11)

| Field | Perlakuan |
|---|---|
| Password (users) | Hash (`bcrypt`), tidak reversibel |
| `two_factor_secret`, `two_factor_recovery_codes` | Dienkripsi oleh Fortify sendiri (`Fortify::currentEncrypter()`, default `APP_KEY`) — **tidak** diberi cast Eloquent tambahan (lihat §13.1 untuk alasannya) |
| `members.phone`, `members.address`, `members.identity_number` | Eloquent cast `encrypted` (`APP_KEY`) |
| `members.identity_number_index` | Blind index (keyed-HMAC, `BLIND_INDEX_KEY` — kunci terpisah dari `APP_KEY`) via `App\Support\BlindIndex`, dipakai untuk cek duplikat NIK tanpa menyimpan nilai yang mudah ditebak (bukan SHA-256 polos) |
| Email (users/members) | **Tidak** dienkripsi (dibutuhkan untuk lookup login/unik) — sesuai spec §11 yang bersyarat |

`APP_KEY`, `BLIND_INDEX_KEY`, dan `DB_PASSWORD` adalah tiga secret terpisah (spec §11) — jangan pernah disamakan.

#### Injection

- Seluruh query lewat Eloquent/Query Builder dengan parameter binding.
- Satu-satunya `DB::statement`/`selectRaw` yang ada (`ReportController::statistics`, loan code generator) menggunakan ekspresi tetap per-driver (bukan menyisipkan input user) dan tidak pernah menggabungkan string dari request.

#### XSS

- Semua output Blade pakai `{{ }}` (auto-escape). Tidak ada `{!! !!}` untuk konten yang berasal dari input user di seluruh aplikasi.
- Content-Security-Policy aktif lewat `SecurityHeaders` middleware (lihat bawah) sebagai lapis pertahanan tambahan.

#### CSRF

- Middleware `web` (termasuk `VerifyCsrfToken`) aktif di seluruh route state-changing — didapat otomatis karena seluruh route terdaftar lewat `routes/web.php`. Tidak ada route yang dikecualikan dari CSRF.
- Diverifikasi struktural di `tests/Feature/SecurityTest.php` (CSRF di-bypass otomatis oleh Laravel saat menjalankan test — lihat komentar di file tersebut untuk penjelasan kenapa pengujian dilakukan secara struktural, bukan dengan memicu 419 secara dinamis).

#### Mass Assignment

- Tidak ada controller yang memanggil `Model::create($request->all())`. Semua input lewat FormRequest (`$request->validated()`/`$request->safe()`).
- Field sensitif (`role`, `is_admin`, `permissions`, `status` approval, dll) hanya diset lewat pemanggilan eksplisit di controller (mis. `$user->syncRoles([...])`), tidak pernah dari array request mentah.

#### Upload File (spec §22)

- MIME divalidasi dari isi file (`mimes:`), bukan ekstensi yang dikirim klien.
- Ukuran dibatasi per jenis file.
- Nama file diacak otomatis oleh `Storage::store()` (Laravel) — nama asli dari klien tidak pernah dipercaya/dipakai sebagai path.
- SVG tidak termasuk daftar MIME yang diizinkan di mana pun (sampul buku, avatar, branding) — mencegah SVG berisi script.
- File privat tidak disimpan di `public/` langsung — semua lewat disk `public` (symlink terkontrol via `storage:link`).

#### Rate Limiting (spec §32)

| Endpoint | Limiter |
|---|---|
| Login | 5/menit per (email+IP), bawaan Fortify |
| 2FA challenge | 5/menit per sesi login, bawaan Fortify |
| Forgot/reset password, verifikasi email, seluruh route Fortify lain | 10/menit per IP (limiter `password-reset`, ditambahkan karena Fortify sendiri tidak men-throttle `password.email`/`password.update`) |
| Lookup barcode (`book-copies.lookup`) | 60/menit per user (limiter `search`) |
| Upload (avatar, sampul buku, branding Pengaturan) | 20/menit per user (limiter `upload`) |

Tidak ada permanent account lockout — sesuai spec §32, mencegah limiter ini disalahgunakan untuk men-DoS akun orang lain.

#### Session Security (spec §33)

- `http_only=true`, `same_site=lax` (default `config/session.php`).
- `SESSION_SECURE_COOKIE=true` **wajib diaktifkan manual** saat production di-serve lewat HTTPS (lihat `.env.example`).
- Session diregenerasi saat login (bawaan Fortify) dan diinvalidasi saat logout.
- `AuthenticateSession` middleware aktif — mengubah password otomatis membatalkan sesi lain.
- "Keluar dari sesi lain" tersedia di halaman Profil (`Auth::logoutOtherDevices()`).

#### Security Headers (spec §34)

Middleware `SecurityHeaders` (global, semua response): `Content-Security-Policy` (termasuk `frame-ancestors 'self'` untuk clickjacking), `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, dan `Strict-Transport-Security` otomatis saat request HTTPS.

#### Error Handling (spec §35)

- Production wajib `APP_DEBUG=false` — tidak ada stack trace/path/SQL/credential yang bocor ke pengguna.
- Halaman error generik sudah tersedia (`resources/views/errors/*`).

#### Audit Trail (spec §24)

- Tabel `activity_logs`, append-only — tidak ada route/tombol update atau delete di UI manapun.
- `App\Support\Activity::log()` **tidak pernah** dipanggil dengan password/OTP/2FA secret/recovery code/token — hanya deskripsi teks dan before/after value non-sensitif (mis. perubahan role, bukan perubahan password).
- Terlihat di menu Administrasi > Audit Log (`audit-logs.view`) dan diringkas di Security Dashboard (`security-dashboard.view`, spec §25).

#### Database & Redis (spec §36/§37)

Tidak diatur dari kode aplikasi (tanggung jawab infrastruktur) — didokumentasikan di README bagian *Deployment Produksi*: gunakan akun DB khusus aplikasi (bukan `root`), least-privilege, tidak diekspos publik; Redis (bila dipakai untuk cache/queue) hanya di jaringan privat.

#### Dependency Security (spec §38)

`composer audit` dan `npm audit` dijalankan sebelum setiap rilis — lihat hasil terbaru di laporan penyelesaian proyek. Tidak ada dependency yang di-*major upgrade* otomatis tanpa peninjauan.

### 13.3 Lampiran — Role & Permission Detail (docs/permissions.md)

Didefinisikan di `database/seeders/RolePermissionSeeder.php` (idempotent — aman dijalankan ulang). Otorisasi runtime **selalu** lewat pemeriksaan permission (`$user->can('...')`) atau Policy, tidak pernah `if ($user->role === '...')` (spec §4/§69).

#### Role

| Role | Ringkasan |
|---|---|
| `super-admin` | Seluruh permission. Satu-satunya yang bisa mengelola User/Role/Permission, Pengaturan (termasuk keamanan), dan melihat Audit Log/Security Dashboard penuh. |
| `admin-perpustakaan` | Kelola penuh master data (buku, eksemplar, kategori, rak), anggota beserta akun loginnya, verifikasi & kelola seluruh transaksi peminjaman/pengembalian/perpanjangan, laporan. |
| `petugas` | Lihat master data, ajukan peminjaman atas nama anggota, verifikasi peminjaman, penyerahan (handover), pengembalian, putuskan perpanjangan. Tidak bisa ubah master data. |
| `anggota` | Lihat katalog, ajukan/batalkan peminjaman sendiri, ajukan perpanjangan, lihat riwayatnya sendiri. |
| `pimpinan` | Read-only — dashboard & laporan saja. |

#### Katalog Permission

```
dashboard.view

books.view / books.create / books.update / books.delete
book-copies.view / book-copies.create / book-copies.update / book-copies.delete
categories.view / categories.create / categories.update / categories.delete
racks.view / racks.create / racks.update / racks.delete

members.view / members.create / members.update / members.delete

loans.create / loans.view-own / loans.view-all / loans.approve /
loans.reject / loans.handover / loans.cancel

loan-extensions.create / loan-extensions.view / loan-extensions.approve

returns.process

reports.view / reports.export

users.view / users.create / users.update / users.disable
roles.manage / permissions.manage
settings.manage
audit-logs.view
security-dashboard.view
```

Katalog ini bersifat *code-defined*: `RolePermissionSeeder` juga **menghapus** permission di database yang sudah tidak ada di daftar ini, beserta penugasannya ke role — mis. sisa `fines.*`, `authors.*`, dan `publishers.*` dari modul yang sudah dihapus.

`loan-extensions.create` hanya dipegang `anggota` (pengaju), sedangkan `loan-extensions.approve` hanya dipegang petugas/admin — satu akun tidak pernah bisa menyetujui pengajuan perpanjangannya sendiri.

#### Menambah Permission Baru

1. Tambahkan nama permission ke `RolePermissionSeeder::PERMISSIONS`.
2. Tambahkan ke role yang relevan di `RolePermissionSeeder::ROLE_PERMISSIONS` (super-admin otomatis dapat semua).
3. Jalankan `php artisan db:seed --class=RolePermissionSeeder`.
4. Gerbangi route (`permission:nama.permission` middleware dan/atau di Policy) dan render menu (`@can('nama.permission')` di `resources/views/layouts/partials/sidenav.blade.php`).

#### Object-Level Authorization (IDOR)

Permission saja tidak cukup untuk mencegah satu anggota melihat data anggota lain (spec §26). Setiap model yang punya "pemilik" (Loan, LoanExtension, Member) menggunakan Policy dengan pola:

```php
public function view(User $user, Loan $loan): bool
{
    if ($user->can('loans.view-all')) {
        return true;
    }

    return $loan->member->user_id === $user->id;
}
```

UUID acak pada resource yang tidak dimiliki menghasilkan **404** (route-model-binding gagal), UUID milik orang lain menghasilkan **403** (Policy menolak) — lihat `tests/Feature/IdorTest.php`.

### 13.4 Lampiran — Alur Peminjaman Detail (docs/loan-flow.md)

#### Diagram Status

```
Anggota mengajukan (CreateLoan)
        │
        ▼
     PENDING ─────────────────┐
        │                     │ reject (RejectLoan, alasan wajib)
        │ approve             ▼
        │ (ApproveLoan:     REJECTED (selesai)
        │  lock+reserve
        │  1 eksemplar/item)
        ▼
     APPROVED ──── tidak diambil sebelum pickup_deadline ────┐
        │                                                     │ (ExpireLoan,
        │ pilih eksemplar + bukti foto                        │  via scheduler)
        │ (HandoverLoan)                                      ▼
        ▼                                                  EXPIRED (selesai,
     BORROWED ──── melewati due_at ────┐                    eksemplar dilepas)
        │     ▲                        │ (MarkOverdueLoans,
        │     │ perpanjangan disetujui │  via scheduler)
        │     │ (ApproveLoanExtension) ▼
        │     └───────────────────── OVERDUE
        │ pilih item + unggah            │
        │ bukti foto (ReturnLoan)        │
        │◄───── pilih item + bukti foto (ReturnLoan) ─────────┘
        ▼
     RETURNED (selesai)

PENDING/APPROVED juga bisa → CANCELLED (CancelLoan — anggota sebelum
diproses, atau petugas/admin dengan loans.approve).
```

Transisi legal didefinisikan terpusat di `App\Enums\LoanStatus::transitions()` — mis. `REJECTED → BORROWED` tidak mungkin terjadi karena setiap Action memvalidasi `canTransitionTo()` sebelum mengubah status (spec §47).

#### Titik Kunci Konkurensi (spec §40)

| Aksi | Lock |
|---|---|
| `ApproveLoan` | `BookCopy::where('book_id', …)->where('status', AVAILABLE)->lockForUpdate()` — dua approval bersamaan untuk buku yang sama tidak bisa mengambil eksemplar yang sama (dites di `LoanFlowTest::test_a_book_copy_cannot_be_allocated_to_two_loans_at_once`). |
| `HandoverLoan` | `BookCopy::where('barcode', …)->lockForUpdate()` sebelum transisi status. |
| `ReturnLoan` | `LoanItem::whereKey(…)->lockForUpdate()` lalu validasi ulang statusnya di dalam transaksi — dua petugas tidak bisa memproses pengembalian item yang sama. |
| `RequestLoanExtension` | Pengajuan PENDING milik peminjaman yang sama di-`lockForUpdate()` — tidak bisa ada dua pengajuan menunggu sekaligus. |
| Pembuatan kode peminjaman | Baris counter harian di `loan_number_sequences` di-`lockForUpdate()` di dalam transaksi (lihat §13.1). |

#### Eligibilitas Peminjaman (`CreateLoan`, spec §14)

Ditolak (`LoanException`, ditangkap controller → flash error) bila:

- Anggota tidak `ACTIVE` atau `expired_at` telah lewat (`Member::isActive()`)
- Jumlah item aktif (PENDING/APPROVED/BORROWED/OVERDUE) + permintaan baru melebihi `max_active_loans`
- `block_if_overdue` aktif **dan** anggota punya peminjaman berstatus OVERDUE
- Salah satu buku tidak aktif atau tidak punya eksemplar berstatus AVAILABLE saat pengajuan (pengecekan awal — alokasi sebenarnya baru terjadi saat approve)

Petugas/admin (`loans.view-all`) yang akunnya tidak terhubung ke data anggota mengajukan **atas nama** anggota: `member_id` wajib diisi di form Pengajuan. Anggota tidak pernah bisa memakai field itu untuk mengajukan atas nama orang lain — `LoanController::store` selalu memakai `member` milik akunnya sendiri bila ada.

#### Perpanjangan / Banding (`RequestLoanExtension`, `ApproveLoanExtension`, `RejectLoanExtension`)

Pengajuan ditolak sistem (`LoanException`) bila: pengaturan `allow_renewal` nonaktif, `max_renewals` sudah tercapai (atau bernilai 0), peminjaman tidak berstatus BORROWED/OVERDUE, atau masih ada pengajuan PENDING untuk peminjaman yang sama.

Saat disetujui:

```
base       = due_at bila masih di masa depan, selain itu now()
new_due_at = base + days
```

`due_at` peminjaman **dan** seluruh item yang belum kembali digeser ke `new_due_at`. Peminjaman OVERDUE kembali ke BORROWED (satu-satunya transisi OVERDUE → BORROWED yang legal di `LoanStatus::transitions()`). Penolakan tidak mengubah jatuh tempo dan wajib menyertakan alasan.

#### Bukti Foto (pengganti scan barcode)

- **Serah terima** — `loan_items.handover_photo_path`, diunggah saat HandoverLoan (opsional) atau menyusul dari halaman **Peminjaman Aktif**.
- **Pengembalian** — `loan_items.return_photo_path`, **wajib**: petugas memilih eksemplar dari daftar peminjaman aktif lalu mengunggah fotonya.

Keduanya disimpan di disk `public` (`storage/app/public/loan-proofs`), divalidasi sebagai gambar (MIME dibaca dari isi file, maks 4 MB) dan lewat rate limiter `upload`.

#### Kondisi Eksemplar Saat Kembali

| Kondisi dipilih petugas | Status eksemplar setelahnya |
|---|---|
| `GOOD` | `AVAILABLE` |
| `MINOR_DAMAGE` | `MAINTENANCE` |
| `MAJOR_DAMAGE` | `DAMAGED` |
| `LOST` | `LOST` |

#### Reminder (spec §19/§20)

Command `loans:process-reminders` (dijadwalkan tiap jam) mengirim tiga jenis notifikasi berdasarkan pengaturan Notifikasi:

- **Pickup** — `pickup_reminder_hours` sebelum `pickup_deadline` (loan APPROVED)
- **Due** — H- sesuai daftar `due_reminder_days` (mis. `[3,1,0]`) dari `due_at` (loan BORROWED)
- **Overdue** — hari-ke sesuai daftar `overdue_reminder_days` (mis. `[1,3,7]`) setelah `due_at` terlewati (loan OVERDUE)

Setiap kombinasi `(loan, type, milestone)` dicatat di tabel `loan_reminders` (unique constraint) agar command aman dijalankan berulang tanpa mengirim reminder yang sama dua kali.

### 13.5 Lampiran — Panduan Instalasi & Deployment (README.md)

#### Requirement

- PHP ^8.2 (proyek ini dikembangkan dengan PHP 8.4)
- Composer 2.x
- MySQL 8.x atau MariaDB 10.x
- Node.js 18+ dan npm (untuk build asset Tailwind)
- Ekstensi PHP standar Laravel (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`)

#### Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

#### Environment (`.env`)

Selain variabel standar Laravel, proyek ini menambahkan beberapa key khusus — lihat `.env.example` untuk daftar lengkap dan komentarnya:

| Variabel | Keterangan |
|---|---|
| `DB_*` | Koneksi MySQL/MariaDB |
| `BLIND_INDEX_KEY` | Kunci HMAC terpisah dari `APP_KEY`, dipakai untuk blind-index pencarian pada field terenkripsi (contoh: NIK anggota). **Wajib diisi**, generate dengan: `php artisan tinker --execute="echo base64_encode(random_bytes(32));"` |
| `SUPER_ADMIN_NAME` / `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` | Dipakai sekali oleh `SuperAdminSeeder`. Kosongkan `SUPER_ADMIN_PASSWORD` agar seeder membuat password acak dan menampilkannya di output (bukan password lemah yang di-hardcode) |
| `FONNTE_TOKEN` / `FONNTE_API_URL` | Kredensial & endpoint gateway WhatsApp Fonnte — lihat §8 dan §12. |

Jangan pernah meng-commit `.env` — sudah masuk `.gitignore`. `APP_KEY`, `BLIND_INDEX_KEY`, dan kredensial database **harus berbeda** antar environment dan tidak boleh digunakan ulang untuk keperluan lain (spec keamanan §11).

#### Migration & Seeding

```bash
php artisan migrate
php artisan db:seed
```

`db:seed` menjalankan berurutan:

1. `RolePermissionSeeder` — membuat seluruh permission (lihat §13.3) dan 5 role default (`super-admin`, `admin-perpustakaan`, `petugas`, `anggota`, `pimpinan`). Idempotent, aman dijalankan berulang.
2. `SuperAdminSeeder` — membuat akun super-admin awal dari `SUPER_ADMIN_EMAIL`/`SUPER_ADMIN_PASSWORD`.
3. `ApplicationSettingSeeder` — mengisi nilai default untuk seluruh pengaturan aplikasi (identitas, branding, peminjaman, notifikasi).
4. `DemoMasterDataSeeder` — data contoh (kategori, rak, buku + eksemplar, anggota beserta akun loginnya). **Otomatis dilewati saat `APP_ENV=production`.** Password anggota demo diambil dari `DEMO_MEMBER_PASSWORD` (default `Anggota123!`).

Untuk reset penuh saat development:

```bash
php artisan migrate:fresh --seed
```

#### Membuat Akun Super Admin

Isi `SUPER_ADMIN_EMAIL` (dan opsional `SUPER_ADMIN_PASSWORD`) di `.env` sebelum menjalankan seeder pertama kali. Jika `SUPER_ADMIN_PASSWORD` dikosongkan, password acak akan dicetak sekali di terminal — segera login dan ganti password. Mengaktifkan 2FA sangat disarankan untuk role ini meskipun tidak diwajibkan sistem.

Untuk membuat/menambah super-admin lain setelah instalasi awal, jalankan ulang seeder ini dengan email baru:

```bash
php artisan db:seed --class=SuperAdminSeeder
```

#### Menjalankan Aplikasi

```bash
php artisan serve
npm run dev   # atau `npm run build` untuk produksi
```

Atau jalankan server + queue worker + log viewer + Vite sekaligus:

```bash
composer run dev
```

#### Queue & Scheduler

Notifikasi (email + in-app + WhatsApp) dikirim lewat queue (`QUEUE_CONNECTION=database` secara default). Jalankan worker:

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

#### Storage & Upload

```bash
php artisan storage:link
```

File upload (sampul buku, logo/favicon/background branding, avatar) disimpan di `storage/app/public`, divalidasi MIME dari isi file (bukan ekstensi kiriman klien), dibatasi ukurannya, dan diberi nama acak (spec §22) — SVG tidak diizinkan.

#### Autentikasi & 2FA

- Registrasi publik **dinonaktifkan** (spec §58) — semua akun dibuat oleh admin lewat menu *Administrasi > User* (staf) atau *Keanggotaan > Anggota* (anggota, dengan opsi "buatkan akun login").
- 2FA (TOTP) bersifat **opsional untuk semua role** — pengguna dapat mengaktifkannya sendiri lewat halaman Profil kapan saja, dan menonaktifkannya kembali tanpa batasan.
- Perubahan role/permission, menonaktifkan 2FA, membuat ulang kode pemulihan, dan mengubah Pengaturan wajib konfirmasi ulang password (spec §6).

#### RBAC (Role & Permission)

Lihat §13.3 untuk daftar lengkap permission dan pemetaan ke role. Otorisasi diperiksa di dua lapis: middleware (`permission:`) di routing, **dan** Laravel Policy per model untuk pemeriksaan object-level (mencegah IDOR — lihat §13.2).

#### Halaman Depan (Landing Page) & SEO

`/` menampilkan landing page publik untuk pengunjung yang belum login (pengguna yang sudah login otomatis diarahkan ke dashboard). Kontennya (judul & subjudul hero, gambar hero, teks "tentang", meta description) diatur Super Admin lewat **Pengaturan Aplikasi > Halaman Depan** — jumlah buku/kategori/anggota dan daftar koleksi terbaru selalu diambil live dari database.

SEO yang sudah diterapkan:

- `<title>`, meta description, canonical URL, Open Graph, dan Twitter card otomatis di setiap halaman (lihat `resources/views/layouts/base.blade.php`)
- Data terstruktur JSON-LD (`schema.org/Library`) di landing page
- `meta robots` bernilai `index, follow` hanya di halaman publik; seluruh halaman aplikasi (di balik login) otomatis `noindex, nofollow`
- `/robots.txt` dan `/sitemap.xml` disajikan dinamis lewat `SeoController` (bukan file statis) agar URL-nya selalu mengikuti `APP_URL` yang sebenarnya

#### Tampilan (Branding & Warna)

Selain logo/favicon/background, Super Admin dapat mengganti **warna utama (primary)** aplikasi lewat color picker di Pengaturan > Branding. Warna ini diterapkan lewat CSS variable (`--color-primary`, lihat `tailwind.config.js` dan `app/Support/Color.php`) sehingga seluruh tombol/link/status aktif ikut berubah **tanpa perlu build ulang asset** setiap kali warna diganti.

#### Menjalankan Test

```bash
php artisan test
```

Test menggunakan SQLite in-memory (dikonfigurasi di `phpunit.xml`) — database MySQL development/production **tidak pernah tersentuh** oleh test suite. Cakupan: autentikasi, RBAC, IDOR, alur peminjaman penuh (termasuk cegah alokasi ganda eksemplar), perpanjangan peminjaman, bukti foto, akun login anggota, render seluruh halaman, manajemen Role & Permission, notifikasi WhatsApp/Fonnte, pengaturan, dan kontrol keamanan (CSRF, mass assignment, validasi upload, anggota nonaktif diblokir).

#### Backup

Dokumentasikan dan uji strategi backup sesuai kebutuhan instansi:

- **Harian**: dump database (`mysqldump` terenkripsi), retensi 14–30 hari.
- **Mingguan**: backup penuh termasuk `storage/app/public` (file upload), retensi 3 bulan.
- **Bulanan**: arsip off-site/terpisah dari server produksi, retensi 1 tahun+.

Simpan key enkripsi backup **terpisah** dari lokasi backup itu sendiri. Uji proses restore secara berkala — backup yang belum pernah diuji restore-nya tidak bisa diandalkan.

#### Deployment Produksi

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

#### Catatan Keamanan

Ringkasan kontrol keamanan yang diimplementasikan ada di §13.2 dan §9. Poin penting:

- Password di-hash (`bcrypt`), tidak pernah disimpan/di-log plaintext.
- Field sensitif (telepon, alamat, NIK anggota) dienkripsi (`encrypted` cast, `APP_KEY`); NIK memakai blind index terpisah (`BLIND_INDEX_KEY`) untuk pengecekan duplikat tanpa menyimpan nilai yang mudah ditebak.
- UUID dipakai di seluruh URL publik — **bukan pengganti otorisasi**; setiap resource tetap melalui Policy.
- Audit trail (`activity_logs`) bersifat append-only, tidak ada tombol edit/delete di UI.
- Rate limiting pada login, 2FA, forgot/reset password, pencarian barcode, dan endpoint upload.
