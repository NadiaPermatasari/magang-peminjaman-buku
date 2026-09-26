# MASTER PROMPT — Sistem Informasi Peminjaman Buku Laravel

## Peran Anda

Bertindaklah sebagai **Senior Laravel Engineer, Software Architect, Database Engineer, dan Application Security Engineer**.

Tugas Anda adalah membangun **Sistem Informasi Peminjaman Buku / Perpustakaan** berbasis **Laravel 12** yang production-ready, modular, aman, mudah dirawat, dan menggunakan prinsip **security-by-design**.

Jangan hanya membuat tampilan atau CRUD dasar. Implementasikan seluruh business flow, validasi, authorization, audit trail, notification, testing, dan kontrol keamanan yang dijelaskan di bawah.

---

# 1. Aturan Kerja Code Agent

Sebelum menulis kode:

1. Analisis seluruh struktur project/repository yang sedang terbuka.
2. Identifikasi:
   - versi Laravel;
   - versi PHP;
   - database;
   - frontend stack;
   - authentication yang sudah tersedia;
   - package yang telah terpasang;
   - struktur route;
   - model;
   - migration;
   - middleware;
   - policy;
   - layout/dashboard;
   - testing framework.
3. Jangan menghapus atau merusak fitur yang sudah ada.
4. Gunakan pola dan style yang sudah digunakan project selama masih layak.
5. Jika repository masih kosong, bangun struktur aplikasi dari awal sesuai requirement ini.
6. Kerjakan secara bertahap dan pastikan setiap tahap dapat dijalankan sebelum lanjut.
7. Jangan membuat dummy implementation untuk requirement penting.
8. Jangan menaruh business logic kompleks di controller.
9. Utamakan:
   - Action/Service class;
   - Form Request;
   - Policy;
   - Eloquent relationship;
   - DB transaction;
   - Queue;
   - Notification;
   - Event/Listener bila diperlukan.
10. Semua authorization wajib dicek di backend. Menyembunyikan tombol di UI **tidak cukup**.
11. Jangan hardcode role ke seluruh source code jika dapat menggunakan permission.
12. Gunakan migration dan seeder sehingga sistem dapat direproduksi.
13. Tambahkan automated test untuk fitur penting dan security-critical.
14. Jangan menganggap pekerjaan selesai sebelum:
   - migration berhasil;
   - seeder berhasil;
   - aplikasi boot tanpa error;
   - test utama lulus;
   - route sensitif memiliki authorization;
   - tidak ada secret yang ikut ter-commit.

Jika terdapat pilihan implementasi, pilih solusi yang paling maintainable dan aman tanpa meminta konfirmasi, kecuali benar-benar mustahil menentukan maksud requirement.

---

# 2. Stack Utama

Gunakan:

- Laravel 13
- PHP versi yang kompatibel dengan Laravel 13
- MySQL atau MariaDB
- Blade + Tailwind CSS atau frontend stack yang sudah digunakan project
- Laravel Starter Kit / Fortify untuk authentication
- `spatie/laravel-permission` untuk RBAC
- Laravel Notification
- Laravel Queue
- Laravel Scheduler
- Laravel Policies
- Laravel Form Requests
- Laravel Events/Listeners bila diperlukan
- PHPUnit atau Pest sesuai project

Untuk fitur tambahan, gunakan package yang mature dan terawat bila benar-benar diperlukan.

Jangan menambahkan package tanpa alasan yang jelas.

---

# 3. Tujuan Sistem

Bangun sistem perpustakaan yang menangani:

- katalog buku;
- eksemplar fisik buku;
- anggota;
- user;
- RBAC;
- pengajuan peminjaman;
- verifikasi peminjaman;
- penyerahan buku;
- pengembalian;
- keterlambatan;
- denda;
- pembayaran/penyelesaian denda;
- reminder;
- notification;
- laporan;
- pengaturan aplikasi;
- 2FA;
- audit trail;
- keamanan aplikasi;
- keamanan data.

---

# 4. Multi Role dan RBAC

Gunakan `spatie/laravel-permission`.

Role default:

1. `super-admin`
2. `admin-perpustakaan`
3. `petugas`
4. `anggota`
5. `pimpinan`

`pimpinan` bersifat read-only dan terutama untuk dashboard/laporan.

## Permission

Buat permission granular minimal sebagai berikut:

```text
dashboard.view

books.view
books.create
books.update
books.delete

book-copies.view
book-copies.create
book-copies.update
book-copies.delete

categories.view
categories.create
categories.update
categories.delete

authors.view
authors.create
authors.update
authors.delete

publishers.view
publishers.create
publishers.update
publishers.delete

racks.view
racks.create
racks.update
racks.delete

members.view
members.create
members.update
members.delete

loans.create
loans.view-own
loans.view-all
loans.approve
loans.reject
loans.handover
loans.cancel

returns.process

fines.view-own
fines.view
fines.mark-paid
fines.waive

reports.view
reports.export

users.view
users.create
users.update
users.disable

roles.manage
permissions.manage

settings.manage

audit-logs.view

security-dashboard.view
```

Gunakan permission pada:

- middleware;
- controller/action;
- policy;
- menu/sidebar;
- tombol tindakan.

Jangan mengandalkan pemeriksaan seperti:

```php
if ($user->role === 'admin')
```

di seluruh aplikasi.

---

# 5. Pembagian Akses Role

## Super Admin

Memiliki seluruh permission.

Hanya Super Admin yang secara default dapat:

- mengatur identitas website;
- mengatur logo;
- favicon;
- login background;
- aturan peminjaman;
- aturan denda;
- notifikasi;
- security settings;
- user;
- role;
- permission;
- melihat audit log lengkap.

## Admin Perpustakaan

Dapat:

- mengelola master buku;
- mengelola eksemplar;
- mengelola anggota;
- melihat semua transaksi;
- verifikasi peminjaman;
- memproses pengembalian;
- memproses pembayaran denda;
- laporan.

Tidak mendapat akses pengaturan security/RBAC kecuali diberikan permission secara eksplisit.

## Petugas

Dapat:

- melihat katalog/master yang diperlukan;
- verifikasi pinjaman;
- penyerahan buku;
- pengembalian;
- denda;
- scan barcode.

## Anggota

Dapat:

- melihat katalog;
- melihat detail buku;
- mengajukan peminjaman;
- membatalkan pengajuan jika masih memungkinkan;
- melihat status peminjaman sendiri;
- melihat riwayat sendiri;
- melihat denda sendiri;
- menerima notifikasi;
- mengatur profil dan 2FA miliknya.

## Pimpinan

Read-only:

- dashboard;
- statistik;
- laporan.

---

# 6. Authentication

Implementasikan:

- login;
- logout;
- reset password;
- email verification bila digunakan;
- password confirmation;
- 2FA;
- recovery code;
- session management.

## Aturan 2FA

2FA wajib untuk:

- super-admin;
- admin-perpustakaan;
- petugas;
- pimpinan.

Untuk anggota, 2FA dapat bersifat opsional.

Gunakan TOTP authenticator melalui Fortify/Laravel authentication stack.

Tindakan sensitif wajib meminta re-authentication/password confirmation, minimal:

- perubahan role;
- perubahan permission;
- menonaktifkan 2FA;
- regenerate recovery code;
- perubahan security setting;
- perubahan data akun sensitif.

---

# 7. Identifier dan UUID

Jangan expose sequential database ID pada URL/API.

Gunakan pola:

```text
id      BIGINT UNSIGNED internal primary key
uuid    UUID UNIQUE public identifier
```

Contoh:

```text
/users/{user:uuid}
/books/{book:uuid}
/loans/{loan:uuid}
/fines/{fine:uuid}
```

Jangan gunakan:

```text
/loans/1
/loans/2
/loans/3
```

untuk public route.

Gunakan UUID untuk semua entity penting:

- users;
- books;
- book_copies;
- categories bila relevan;
- members;
- loans;
- loan_items;
- fines;
- fine_payments;
- notifications custom bila diperlukan;
- activity/security logs.

UUID hanyalah public identifier.

**UUID tidak menggantikan authorization.**

Setiap resource tetap wajib melalui Policy/RBAC.

---

# 8. Data Buku

Pisahkan antara judul buku dan eksemplar fisik.

## `books`

Minimal:

```text
id
uuid
isbn
title
slug
category_id
publisher_id
publication_year
edition
language
page_count
description
cover_path
rack_id nullable
is_active
created_by
updated_by
created_at
updated_at
deleted_at
```

Relasi dengan author sebaiknya many-to-many.

## `book_copies`

Minimal:

```text
id
uuid
book_id
barcode
inventory_code
acquisition_date
source
condition
status
notes
created_at
updated_at
deleted_at
```

Status:

```text
AVAILABLE
RESERVED
BORROWED
MAINTENANCE
DAMAGED
LOST
INACTIVE
```

Barcode harus unik.

Sediakan pencarian barcode dengan cepat.

---

# 9. Master Data

Buat modul:

- kategori;
- author;
- publisher;
- rak;
- buku;
- eksemplar buku.

Gunakan:

- pagination;
- search;
- filter;
- sorting;
- soft delete jika sesuai;
- authorization;
- validation.

---

# 10. Anggota

Data anggota minimal:

```text
id
uuid
user_id nullable
member_number
name
email
phone
address
identity_number nullable
status
joined_at
expired_at nullable
notes
created_at
updated_at
deleted_at
```

Status minimal:

```text
ACTIVE
INACTIVE
SUSPENDED
```

Sistem harus dapat memblokir peminjaman jika anggota:

- inactive;
- suspended;
- melewati masa aktif;
- mencapai batas pinjaman;
- memiliki overdue sesuai konfigurasi;
- memiliki denda belum terselesaikan jika rule tersebut aktif.

---

# 11. Enkripsi Data

Terapkan field-level encryption pada data sensitif.

Jangan mengenkripsi semua kolom tanpa alasan.

Data yang layak dienkripsi antara lain:

- nomor telepon;
- alamat;
- NIK/identity number;
- secret 2FA;
- recovery code;
- API credential;
- integration credential;
- sensitive notes.

Password **HARUS di-hash**, bukan dienkripsi reversible.

Gunakan Laravel Hash.

Jangan menyimpan password plaintext.

Untuk field terenkripsi yang perlu exact-match lookup seperti email bila email ikut dienkripsi, gunakan pola:

```text
email_encrypted
email_index
```

`email_index` dibuat menggunakan keyed HMAC dari email yang telah dinormalisasi.

Jangan menggunakan SHA256 biasa sebagai searchable index untuk data yang mudah ditebak.

Pisahkan key:

```text
APP_KEY
BLIND_INDEX_KEY
BACKUP_KEY
DATABASE_PASSWORD
```

Jangan gunakan satu key/password untuk semua kebutuhan.

Jangan hardcode secret di source code.

---

# 12. Peminjaman — Business Flow

Flow utama:

```text
Anggota
   ↓
Pilih Buku
   ↓
Ajukan Pinjaman
   ↓
PENDING
   ↓
Petugas Verifikasi
   ├── REJECTED
   └── APPROVED
          ↓
      Book Copy RESERVED
          ↓
      Anggota Mengambil
          ↓
      Petugas Scan Barcode
          ↓
      BORROWED
          ↓
      Reminder
          ↓
    ┌─────────────┐
    │             │
RETURNED       OVERDUE
                  ↓
             Hitung Denda
                  ↓
              RETURNED
```

Status loan minimal:

```text
PENDING
APPROVED
REJECTED
BORROWED
OVERDUE
RETURNED
EXPIRED
CANCELLED
```

---

# 13. Struktur Loan

## `loans`

Minimal:

```text
id
uuid
code
member_id
status
requested_at
approved_by nullable
approved_at nullable
rejected_by nullable
rejected_at nullable
rejection_reason nullable
pickup_deadline nullable
borrowed_at nullable
due_at nullable
returned_at nullable
cancelled_at nullable
expired_at nullable
notes nullable
created_at
updated_at
```

Kode transaksi human-readable, misalnya:

```text
PJ-20260925-000001
```

Pastikan unik dan aman terhadap concurrency.

## `loan_items`

Minimal:

```text
id
uuid
loan_id
book_id
book_copy_id nullable
status
borrowed_at nullable
due_at nullable
returned_at nullable
condition_on_borrow nullable
condition_on_return nullable
notes nullable
created_at
updated_at
```

Satu pengajuan dapat berisi lebih dari satu buku.

---

# 14. Verifikasi Peminjaman

Ketika anggota mengajukan peminjaman:

Validasi:

- member aktif;
- buku aktif;
- jumlah buku tersedia;
- batas jumlah pinjaman;
- pinjaman aktif;
- overdue;
- denda belum selesai;
- aturan lain dari settings.

Status awal:

```text
PENDING
```

Petugas dapat:

```text
APPROVE
REJECT
```

Jika reject:

- alasan wajib;
- kirim notification.

Jika approve:

- alokasikan `book_copy`;
- ubah copy menjadi `RESERVED`;
- set `pickup_deadline`;
- kirim notification ke anggota.

Gunakan DB transaction.

Gunakan row locking bila diperlukan untuk mencegah dua transaksi mengalokasikan eksemplar yang sama.

Contoh pola:

```php
DB::transaction(function () {
    $copy = BookCopy::query()
        ->where(...)
        ->where('status', 'AVAILABLE')
        ->lockForUpdate()
        ->firstOrFail();

    // reserve
});
```

---

# 15. Penyerahan Buku

Ketika anggota datang:

1. petugas membuka transaksi approved;
2. scan/input barcode;
3. sistem memastikan barcode adalah copy yang dialokasikan atau copy valid;
4. petugas konfirmasi kondisi buku;
5. status copy:
   `RESERVED -> BORROWED`;
6. loan menjadi `BORROWED`;
7. sistem menentukan due date;
8. simpan actor dan timestamp;
9. audit log.

---

# 16. Pengembalian

Petugas:

1. scan barcode;
2. sistem menemukan loan aktif;
3. tampilkan:
   - anggota;
   - tanggal pinjam;
   - jatuh tempo;
   - keterlambatan;
   - estimasi denda;
4. petugas memilih kondisi buku saat kembali;
5. sistem hitung denda final;
6. loan item menjadi returned;
7. book copy menjadi:
   - `AVAILABLE`, atau
   - `DAMAGED`, atau
   - `MAINTENANCE`, atau
   - `LOST`;
8. jika semua item selesai, loan menjadi `RETURNED`;
9. simpan audit log;
10. kirim notification.

Gunakan transaction.

---

# 17. Keterlambatan

Scheduler harus secara periodik mengecek:

```text
BORROWED
due_at < now()
```

dan mengubah status menjadi:

```text
OVERDUE
```

sesuai business rule.

Jangan mengandalkan user membuka halaman untuk memperbarui status overdue.

---

# 18. Denda

Konfigurasi denda tidak boleh hardcoded.

Super Admin dapat mengatur:

```text
fine_enabled
fine_amount_per_day
grace_period_days
block_new_loan_if_unpaid_fine
maximum_fine nullable
```

## `fines`

Minimal:

```text
id
uuid
loan_item_id
member_id
type
late_days
rate
amount
status
calculated_at
paid_at nullable
waived_at nullable
waived_by nullable
waive_reason nullable
created_at
updated_at
```

Status:

```text
UNPAID
PAID
WAIVED
CANCELLED
```

Permission khusus:

```text
fines.mark-paid
fines.waive
```

Pembebasan denda:

- hanya user berpermission;
- wajib alasan;
- wajib audit log;
- nilai sebelum/sesudah tercatat.

Jika pembayaran denda membutuhkan histori terpisah, buat `fine_payments`.

---

# 19. Notification

Gunakan Laravel Notification.

Minimal channel:

1. database/in-app;
2. email.

Struktur harus mudah dikembangkan untuk WhatsApp di masa depan.

Notifikasi minimal:

- pengajuan berhasil dibuat;
- ada pengajuan baru untuk petugas;
- peminjaman disetujui;
- peminjaman ditolak;
- batas pengambilan hampir habis;
- H-3 jatuh tempo;
- H-1 jatuh tempo;
- hari jatuh tempo;
- overdue;
- pengembalian berhasil;
- denda dibuat;
- denda dibayar/diselesaikan.

Gunakan queue untuk notification yang sesuai.

Pastikan job idempotent agar reminder tidak terkirim berulang tanpa kontrol.

Simpan penanda/reminder log apabila diperlukan.

---

# 20. Scheduler

Buat scheduled command/job untuk:

- reminder jatuh tempo;
- penandaan overdue;
- expired approved loan yang tidak diambil;
- reminder denda bila diperlukan.

Jangan hardcode seluruh jadwal.

Reminder harus mengambil konfigurasi dari settings.

Contoh settings:

```text
due_reminder_days = [3,1,0]
overdue_reminder_days = [1,3,7]
pickup_reminder_hours
pickup_expiration_days
```

---

# 21. System Settings

Buat settings yang dapat dikelola Super Admin.

Kelompok:

## Identitas Aplikasi

```text
app_name
app_short_name
institution_name
address
phone
email
description
footer_text
```

## Branding

```text
logo
login_logo
favicon
login_background
```

## Peminjaman

```text
loan_duration_days
max_active_loans
pickup_deadline_days
allow_renewal
max_renewals
block_if_overdue
```

## Denda

```text
fine_enabled
fine_amount_per_day
fine_grace_period
maximum_fine
block_if_unpaid_fine
```

## Notification

```text
email_notification_enabled
due_reminder_days
overdue_reminder_days
```

## Security

Hanya setting yang aman untuk dikonfigurasi melalui UI.

**Jangan tampilkan atau simpan APP_KEY dari dashboard.**

Secret server-level tetap melalui environment/secret management.

Buat helper/service yang efisien untuk membaca settings dan gunakan cache.

Invalidate cache saat settings berubah.

---

# 22. Upload File

File upload:

- logo;
- favicon;
- background;
- cover buku.

Wajib:

- validasi MIME;
- validasi extension;
- limit size;
- randomized filename;
- jangan percaya nama file user;
- jangan mengizinkan executable;
- jangan menyimpan file private pada public folder jika tidak diperlukan;
- hindari SVG upload jika sanitasi belum diterapkan;
- cegah path traversal;
- jangan mengeksekusi file upload.

Cover boleh menerima format aman seperti:

```text
jpg
jpeg
png
webp
```

Favicon sesuai kebutuhan yang aman.

---

# 23. Soft Delete dan Histori

Gunakan soft delete pada master data bila sesuai:

- users;
- members;
- books;
- book copies;
- categories;
- publishers;
- authors;
- racks.

Untuk data transaksi:

- loans;
- loan_items;
- fines;
- fine_payments;
- audit logs;

jangan menyediakan hard delete biasa melalui UI.

Histori transaksi harus dapat diaudit.

---

# 24. Audit Trail

Buat audit/activity logging untuk tindakan sensitif.

Minimal catat:

```text
LOGIN_SUCCESS
LOGIN_FAILED
LOGOUT

2FA_ENABLED
2FA_DISABLED
2FA_FAILED
RECOVERY_CODES_REGENERATED

PASSWORD_CHANGED

USER_CREATED
USER_UPDATED
USER_DISABLED

ROLE_ASSIGNED
ROLE_REMOVED
PERMISSION_CHANGED

BOOK_CREATED
BOOK_UPDATED
BOOK_DELETED

BOOK_COPY_CREATED
BOOK_COPY_UPDATED

LOAN_CREATED
LOAN_APPROVED
LOAN_REJECTED
LOAN_HANDED_OVER
LOAN_RETURNED
LOAN_CANCELLED
LOAN_EXPIRED

FINE_CREATED
FINE_MARKED_PAID
FINE_WAIVED

SETTING_CHANGED
```

Simpan bila relevan:

```text
actor
action
subject
subject_uuid
ip_address
user_agent
old_values
new_values
created_at
```

Redact/filter data sensitif.

**Jangan pernah log:**

- password;
- OTP;
- 2FA secret;
- recovery code;
- APP_KEY;
- blind index key;
- database password;
- bearer token;
- session ID/cookie;
- credential API.

Audit log bersifat append-oriented.

Jangan sediakan tombol edit/delete audit log melalui UI normal.

---

# 25. Security Dashboard

Super Admin atau role dengan permission dapat melihat ringkasan:

- failed login hari ini;
- successful login;
- 2FA failure;
- blocked/rate-limited request jika datanya tersedia;
- active users/session bila implementasi memungkinkan;
- security event terbaru.

Jangan tampilkan secret.

---

# 26. Proteksi IDOR / Broken Access Control

Ini requirement kritis.

Contoh:

Anggota A membuka:

```text
/loans/{uuid-milik-anggota-B}
```

hasil wajib:

```text
403 Forbidden
```

kecuali user memiliki:

```text
loans.view-all
```

Gunakan Laravel Policy.

Contoh prinsip:

```php
public function view(User $user, Loan $loan): bool
{
    if ($user->can('loans.view-all')) {
        return true;
    }

    return $loan->member->user_id === $user->id;
}
```

Terapkan prinsip serupa pada:

- fine;
- member;
- loan;
- notification;
- profile;
- document/resource lain.

Tambahkan automated tests untuk IDOR.

---

# 27. SQL Injection

Gunakan:

- Eloquent;
- Query Builder;
- parameter binding.

Hindari query raw yang menggabungkan user input.

Jangan:

```php
DB::select("SELECT * FROM users WHERE name = '$name'");
```

Jika raw query wajib, gunakan binding.

---

# 28. XSS

Gunakan escaped Blade output:

```blade
{{ $value }}
```

Jangan gunakan:

```blade
{!! $value !!}
```

untuk user-controlled content kecuali telah disanitasi.

Jika ada rich text editor:

- sanitize HTML;
- gunakan allowlist;
- cegah script/event attribute/javascript URL.

Tambahkan Content Security Policy yang layak.

---

# 29. CSRF

Semua route web state-changing wajib melalui CSRF middleware.

Gunakan:

```blade
@csrf
```

Jangan menambahkan route sensitif ke CSRF exception tanpa alasan yang kuat.

---

# 30. Mass Assignment

Jangan menggunakan request mentah:

```php
Model::create($request->all());
```

Gunakan:

- FormRequest;
- validated data;
- explicit DTO/array;
- `$fillable` yang benar.

Pastikan user tidak dapat mengirim field seperti:

```text
is_admin
role
permission
status
approved_by
fine_amount
```

tanpa authorization khusus.

---

# 31. Input Validation

Gunakan Form Request untuk input utama.

Validasi:

- type;
- max length;
- enum/status;
- date;
- UUID;
- relational existence;
- uniqueness;
- file MIME/size;
- business rule.

Jangan percaya data dari frontend.

---

# 32. Rate Limiting dan Brute Force

Terapkan rate limit pada minimal:

- login;
- forgot password;
- reset password;
- 2FA verification;
- resend verification;
- API;
- search endpoint berat;
- upload endpoint;
- endpoint sensitif.

Gunakan kombinasi identifier yang wajar:

- IP;
- username/email hash;
- user ID.

Jangan membuat permanent account lock hanya karena beberapa gagal login karena dapat disalahgunakan untuk DoS terhadap user lain.

---

# 33. Session Security

Production harus mendukung:

```text
HTTPS only
Secure cookie
HttpOnly cookie
SameSite policy
session regeneration after login
session invalidation after logout
```

Tambahkan idle timeout yang masuk akal untuk role admin.

Jika memungkinkan, sediakan:

```text
Logout semua perangkat
```

untuk user.

---

# 34. Security Headers

Implementasikan header keamanan yang sesuai, minimal pertimbangkan:

```text
Strict-Transport-Security
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Permissions-Policy
```

Gunakan CSP `frame-ancestors` untuk clickjacking protection.

Jangan menerapkan CSP sembarangan sampai merusak asset aplikasi.

Sesuaikan dengan frontend yang digunakan.

---

# 35. Error Handling

Production:

```env
APP_ENV=production
APP_DEBUG=false
```

Jangan expose:

- stack trace;
- filesystem path;
- SQL statement;
- database credential;
- environment;
- internal class detail.

Tampilkan generic error page kepada user.

Log detail error secara internal dengan correlation/error ID jika memungkinkan.

---

# 36. Database Security

Database production:

- jangan expose port MySQL/MariaDB langsung ke internet;
- gunakan firewall/private network;
- gunakan DB account khusus aplikasi;
- jangan gunakan `root`;
- berikan least privilege;
- gunakan password kuat;
- backup terenkripsi;
- batasi akses administrative.

Jangan menyimpan credential database di repository.

---

# 37. Redis / Queue Security

Jika menggunakan Redis:

- jangan expose Redis publik;
- gunakan local/private network;
- authentication jika environment membutuhkan;
- Redis dapat digunakan untuk cache/queue/session/rate limit.

Pastikan queue worker production terdokumentasi.

---

# 38. Dependency Security

Sebelum final:

Jalankan security/dependency checks yang tersedia seperti:

```bash
composer audit
npm audit
```

Jangan otomatis melakukan major upgrade yang dapat merusak aplikasi tanpa analisis.

Pastikan package tidak abandoned atau memiliki vulnerability kritis yang diketahui jika alternatif layak tersedia.

---

# 39. Backup

Dokumentasikan strategi:

```text
daily
weekly
monthly
```

Backup database harus:

- terenkripsi;
- disimpan di lokasi terpisah/offsite bila production;
- memiliki retention;
- diuji proses restore.

Jangan menyimpan encryption key backup bersama backup itu sendiri.

---

# 40. Race Condition / Concurrency

Gunakan transaction + locking pada operasi penting:

- approve peminjaman;
- reserve eksemplar;
- serah terima;
- pengembalian;
- pembayaran denda;
- generate nomor transaksi bila perlu.

Pastikan satu `book_copy` tidak dapat dipinjam oleh dua transaksi sekaligus.

---

# 41. Menu Dashboard

## Semua User

```text
Dashboard
Katalog
Notifikasi
Profil
Keamanan Akun / 2FA
```

## Admin/Petugas sesuai permission

```text
Master Data
 ├─ Buku
 ├─ Eksemplar Buku
 ├─ Kategori
 ├─ Penulis
 ├─ Penerbit
 └─ Rak

Keanggotaan
 └─ Anggota

Transaksi
 ├─ Pengajuan
 ├─ Menunggu Verifikasi
 ├─ Siap Diambil
 ├─ Peminjaman Aktif
 ├─ Pengembalian
 ├─ Keterlambatan
 └─ Denda

Laporan
 ├─ Peminjaman
 ├─ Pengembalian
 ├─ Keterlambatan
 ├─ Denda
 └─ Statistik
```

## Super Admin

Tambahkan:

```text
Administrasi
 ├─ User
 ├─ Role
 ├─ Permission
 └─ Audit Log

Pengaturan
 ├─ Identitas Aplikasi
 ├─ Branding
 ├─ Peminjaman
 ├─ Denda
 ├─ Notifikasi
 └─ Keamanan

Security
 └─ Security Dashboard
```

Render menu berdasarkan permission.

---

# 42. Dashboard Anggota

Tampilkan:

- jumlah buku sedang dipinjam;
- pengajuan pending;
- buku hampir jatuh tempo;
- buku overdue;
- total denda belum selesai;
- notification terbaru;
- daftar pinjaman aktif;
- riwayat.

---

# 43. Dashboard Petugas

Tampilkan:

- pengajuan menunggu verifikasi;
- buku siap diambil;
- jatuh tempo hari ini;
- overdue;
- pengembalian hari ini;
- denda belum selesai;
- shortcut scan barcode;
- shortcut verifikasi.

---

# 44. Dashboard Super Admin

Tampilkan:

- total judul;
- total eksemplar;
- total anggota;
- peminjaman aktif;
- pengajuan pending;
- overdue;
- pengembalian;
- denda;
- statistik;
- grafik peminjaman;
- kategori populer;
- buku populer;
- security event.

Gunakan query yang efisien.

Hindari N+1 query.

---

# 45. Laporan

Minimal laporan:

- peminjaman per periode;
- pengembalian;
- overdue;
- denda;
- buku paling banyak dipinjam;
- anggota aktif;
- kondisi eksemplar;
- statistik kategori.

Filter minimal:

- tanggal;
- status;
- anggota;
- buku;
- kategori.

Export bila permission:

```text
reports.export
```

Implementasikan format export sesuai stack/project, misalnya Excel/PDF jika package yang layak tersedia.

---

# 46. Database Index

Tambahkan index pada kolom yang sering digunakan.

Pertimbangkan index pada:

```text
uuid
barcode
member_number
status
due_at
requested_at
created_at
book_id
member_id
loan_id
book_copy_id
email_index
```

Gunakan composite index bila query memang membutuhkannya.

Jangan menambahkan index tanpa pertimbangan.

---

# 47. Enum / Status

Jangan menyebarkan magic string secara acak.

Gunakan PHP backed Enum jika sesuai:

```text
BookCopyStatus
LoanStatus
FineStatus
MemberStatus
BookCondition
```

Centralize transitions/business rules.

Cegah illegal state transition.

Contoh:

```text
REJECTED -> BORROWED
```

harus ditolak.

---

# 48. Business Action Classes

Pisahkan logic kompleks menjadi Action/Service, misalnya:

```text
app/Actions/Loans/CreateLoan.php
app/Actions/Loans/ApproveLoan.php
app/Actions/Loans/RejectLoan.php
app/Actions/Loans/HandoverLoan.php
app/Actions/Loans/ReturnLoan.php
app/Actions/Loans/ExpireLoan.php

app/Actions/Fines/CalculateFine.php
app/Actions/Fines/MarkFinePaid.php
app/Actions/Fines/WaiveFine.php

app/Actions/Security/
```

Controller harus tipis.

---

# 49. Form Requests

Contoh:

```text
StoreBookRequest
UpdateBookRequest
StoreBookCopyRequest
StoreLoanRequest
ApproveLoanRequest
RejectLoanRequest
HandoverLoanRequest
ReturnLoanRequest
MarkFinePaidRequest
WaiveFineRequest
UpdateSettingsRequest
```

Gunakan method `authorize()` dan rules yang tepat.

---

# 50. Policy

Minimal:

```text
BookPolicy
BookCopyPolicy
MemberPolicy
LoanPolicy
FinePolicy
UserPolicy
SettingPolicy
AuditLogPolicy
```

Jangan hanya mengandalkan middleware permission.

Policy diperlukan untuk object-level authorization.

---

# 51. Notification Classes

Contoh:

```text
LoanSubmittedNotification
LoanApprovedNotification
LoanRejectedNotification
LoanPickupReminderNotification
LoanDueReminderNotification
LoanOverdueNotification
LoanReturnedNotification
FineCreatedNotification
FinePaidNotification
```

Gunakan queue bila tepat.

---

# 52. Console Commands / Jobs

Buat command/job yang jelas untuk scheduled operation:

```text
ProcessLoanReminders
MarkOverdueLoans
ExpireApprovedLoans
SendOverdueReminders
```

Pastikan aman dijalankan berulang.

Gunakan locking/idempotency bila diperlukan.

---

# 53. Testing

Buat test minimal untuk:

## Authentication

- login berhasil;
- login gagal;
- protected page membutuhkan login;
- role sensitif membutuhkan 2FA sesuai implementasi.

## RBAC

- anggota tidak dapat membuka admin;
- petugas tidak dapat manage role;
- super-admin dapat manage settings;
- pimpinan read-only.

## IDOR

- anggota A tidak dapat melihat loan anggota B;
- anggota A tidak dapat melihat fine anggota B;
- UUID acak menghasilkan 404;
- permission `view-all` bekerja.

## Loan

- create pending;
- approve;
- reject;
- reserve copy;
- handover;
- return;
- overdue;
- expire;
- copy tidak dapat dialokasikan dua kali.

## Fine

- hitung keterlambatan;
- mark paid;
- waive membutuhkan permission;
- waive reason wajib.

## Settings

- hanya authorized user dapat update;
- cache ter-refresh setelah update.

## Security

- protected POST membutuhkan CSRF pada layer web;
- mass assignment field sensitif tidak lolos;
- upload invalid file ditolak;
- inactive member tidak dapat pinjam.

Gunakan database testing strategy yang tidak merusak production.

---

# 54. Seeder

Buat seeder:

```text
RolePermissionSeeder
SuperAdminSeeder
ApplicationSettingSeeder
DemoMasterDataSeeder optional
```

Seeder default permission harus idempotent.

Jika membuat Super Admin default:

Jangan hardcode password permanen yang lemah.

Gunakan environment variable atau instruksi aman untuk membuat akun awal.

---

# 55. Environment

Buat `.env.example` yang lengkap namun tanpa secret.

Contoh kebutuhan:

```text
APP_NAME
APP_ENV
APP_KEY

DB_*

QUEUE_CONNECTION
CACHE_STORE
SESSION_DRIVER

MAIL_*

BLIND_INDEX_KEY

LOG_CHANNEL
```

Jangan commit `.env`.

---

# 56. Production Deployment

Buat dokumentasi deployment minimal:

```text
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force # bila diperlukan
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Jelaskan setup:

- queue worker;
- scheduler;
- HTTPS;
- writable directories;
- ownership/permissions;
- backup;
- log rotation.

Scheduler production:

```cron
* * * * * php /path/to/artisan schedule:run
```

Gunakan supervisor/systemd untuk queue bila sesuai.

Jangan menjalankan web server sebagai root.

---

# 57. Security Baseline

Gunakan **OWASP ASVS Level 2** sebagai baseline desain.

Perhatikan minimal risiko:

- Broken Access Control;
- Security Misconfiguration;
- Supply Chain/Dependency Risk;
- Cryptographic Failure;
- Injection;
- Authentication Failure;
- Integrity Failure;
- Logging/Alerting Failure;
- Error Handling;
- XSS;
- CSRF;
- IDOR;
- brute force;
- session hijacking;
- malicious upload;
- privilege escalation.

Jangan sekadar menulis checklist.

Implementasikan mitigasinya dalam code/configuration sesuai kebutuhan aplikasi.

---

# 58. Secure Defaults

Default sistem harus aman.

Contoh:

```text
registration public = OFF
2FA admin = REQUIRED
APP_DEBUG = false production
fine waiver = restricted
role management = super-admin
audit log delete UI = none
database public access = none
unrestricted file upload = none
```

Jangan membuat fitur administrasi terbuka hanya karena mudah saat development.

---

# 59. Database Schema yang Diharapkan

Minimal tabel:

```text
users

roles
permissions
model_has_roles
model_has_permissions
role_has_permissions

members

categories
authors
publishers
racks

books
author_book
book_copies

loans
loan_items

fines
fine_payments

settings

notifications

activity_logs
security_events optional

jobs
failed_jobs jika queue database digunakan
```

Sesuaikan jika package menyediakan tabelnya sendiri.

---

# 60. UX Transaksi

Pastikan status mudah dipahami.

Gunakan badge:

```text
Menunggu Verifikasi
Disetujui
Siap Diambil
Sedang Dipinjam
Terlambat
Selesai
Ditolak
Kedaluwarsa
Dibatalkan
```

Untuk tindakan kritis tampilkan confirmation dialog.

Contoh:

```text
Setujui Peminjaman?
Tindakan ini akan mereservasi eksemplar buku.
```

Untuk reject:

```text
Alasan penolakan wajib diisi.
```

---

# 61. Barcode

Setiap physical copy memiliki barcode unik.

Sediakan workflow:

```text
scan barcode
     ↓
lookup book copy
     ↓
show title + status
```

Gunakan pada:

- penyerahan;
- pengembalian;
- inventory lookup.

Pastikan input scanner dianggap sebagai input user dan tetap divalidasi.

---

# 62. Query dan Performance

Gunakan:

- eager loading;
- pagination;
- indexes;
- query scopes;
- cached settings;
- queue untuk pekerjaan lambat.

Hindari:

- N+1;
- load seluruh tabel;
- query berat setiap render dashboard;
- decrypt data massal yang tidak diperlukan.

---

# 63. Logging

Bedakan:

```text
application log
audit log
security event
```

Jangan mencampurkan semua fungsi.

Gunakan severity yang sesuai.

Jangan log data sensitif.

---

# 64. Coding Standard

Gunakan:

- PSR-compatible formatting;
- `declare(strict_types=1)` jika project menggunakannya;
- type hints;
- return type;
- enum;
- readonly/value object bila bermanfaat;
- clean naming;
- small methods;
- minimal duplication.

Gunakan Laravel Pint jika tersedia.

Jalankan:

```bash
./vendor/bin/pint
```

sebelum final bila sesuai project.

---

# 65. Dokumentasi

Buat/update `README.md` yang menjelaskan:

1. gambaran aplikasi;
2. requirement;
3. instalasi;
4. `.env`;
5. migration;
6. seeding;
7. membuat super admin;
8. queue;
9. scheduler;
10. storage;
11. 2FA;
12. RBAC;
13. backup;
14. deployment;
15. security notes;
16. menjalankan test.

Buat juga dokumentasi:

```text
docs/architecture.md
docs/security.md
docs/permissions.md
docs/loan-flow.md
```

jika struktur project memungkinkan.

---

# 66. Urutan Implementasi

Kerjakan dengan urutan berikut.

## Phase 1 — Audit Project

- baca project;
- cek dependency;
- tentukan struktur;
- identifikasi risiko perubahan.

## Phase 2 — Foundation

- auth;
- UUID;
- RBAC;
- roles;
- permissions;
- policy foundation;
- 2FA;
- settings infrastructure.

## Phase 3 — Master

- kategori;
- author;
- publisher;
- rack;
- books;
- book copies;
- member.

## Phase 4 — Loan

- submission;
- verification;
- rejection;
- approval;
- reservation;
- handover;
- return;
- overdue;
- expiration.

## Phase 5 — Fine

- calculation;
- status;
- payment;
- waive;
- audit.

## Phase 6 — Notification

- database notification;
- email;
- queue;
- reminders;
- scheduler.

## Phase 7 — Security Hardening

- encryption;
- authorization review;
- rate limit;
- session;
- upload;
- headers;
- secure errors;
- logging;
- dependency audit.

## Phase 8 — Dashboard & Report

- member dashboard;
- staff dashboard;
- admin dashboard;
- reports.

## Phase 9 — Testing

- feature tests;
- security tests;
- workflow tests;
- concurrency-related tests jika memungkinkan.

## Phase 10 — Documentation & Production Readiness

- README;
- deployment;
- scheduler;
- queue;
- backup;
- security notes.

---

# 67. Acceptance Criteria

Project baru dianggap selesai jika:

- aplikasi boot dengan benar;
- migration berhasil;
- seeder berhasil;
- authentication bekerja;
- RBAC bekerja;
- 2FA bekerja;
- anggota dapat mengajukan pinjaman;
- petugas dapat approve/reject;
- copy otomatis reserved;
- copy tidak dapat dialokasikan ganda;
- penyerahan bekerja;
- pengembalian bekerja;
- overdue bekerja;
- denda dihitung benar;
- reminder bekerja melalui scheduler;
- notification masuk;
- settings dapat diubah Super Admin;
- logo/favicon/name berubah dari settings;
- UUID digunakan pada public route;
- unauthorized access ditolak;
- IDOR test lulus;
- sensitive fields encrypted sesuai desain;
- password hashed;
- audit log tercatat;
- malicious upload ditolak;
- rate limiting tersedia;
- APP_DEBUG tidak diperlukan di production;
- dependency audit diperiksa;
- automated test utama lulus;
- dokumentasi tersedia.

---

# 68. Output Code Agent

Selama pengerjaan, laporkan secara ringkas:

```text
Phase yang sedang dikerjakan
File utama yang dibuat/diubah
Migration yang ditambahkan
Route yang ditambahkan
Security control yang diterapkan
Test yang dibuat
Masalah yang ditemukan
```

Jangan berhenti hanya karena satu masalah kecil.

Perbaiki masalah yang dapat diperbaiki sendiri.

Pada akhir pekerjaan berikan laporan:

```text
1. Fitur yang selesai
2. Struktur database
3. Role & permission
4. Security control
5. Test result
6. Dependency/security audit result
7. Command deployment
8. Environment variable baru
9. Hal yang masih perlu konfigurasi server/external service
```

---

# 69. Larangan

JANGAN:

- menyimpan password plaintext;
- menyimpan secret ke git;
- menggunakan sequential ID pada public URL jika UUID tersedia;
- menganggap UUID sebagai pengganti Policy;
- mengandalkan hide/show menu sebagai authorization;
- memberi admin akses hanya dengan parameter request;
- menggunakan `$request->all()` untuk mass assignment;
- membuat SQL dari concatenated user input;
- menggunakan `{!! !!}` pada user-generated content tanpa sanitasi;
- membuat upload bebas;
- membuat DB/Redis terbuka publik;
- mematikan CSRF untuk mempermudah development;
- membiarkan `APP_DEBUG=true` di production;
- mencatat password/token/OTP ke log;
- hard delete histori transaksi;
- membuat semua user menjadi super-admin;
- menaruh business logic besar di controller;
- mengabaikan race condition saat reservasi buku;
- menganggap pekerjaan selesai hanya karena halaman tampil.

---

# 70. Prinsip Akhir

Prioritas implementasi:

```text
1. Security
2. Data integrity
3. Authorization
4. Correct business flow
5. Maintainability
6. Auditability
7. Performance
8. User experience
```

Jika ada konflik antara kemudahan implementasi dan keamanan/data integrity, pilih solusi yang aman dan dapat dipertanggungjawabkan.

Bangun aplikasi ini seolah akan digunakan pada lingkungan organisasi/instansi nyata dan menyimpan data pengguna yang harus dilindungi.

Mulai dengan menganalisis repository yang sedang terbuka, kemudian implementasikan Phase 1 sampai Phase 10 secara sistematis tanpa merusak fitur yang sudah ada.
