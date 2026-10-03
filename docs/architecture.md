# Arsitektur

## Struktur Domain

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

## Alur Request Khas

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

## Keputusan Desain Penting

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

## Katalog vs Master Data "Buku"

Ada dua rute berbeda untuk buku yang sengaja dipisah:

- `CatalogController` (`/catalog`) — tampilan baca-saja untuk **semua** pengguna terautentikasi (spec §41 "Semua User > Katalog"), tidak melalui `BookPolicy`.
- `BookController` (`/books`, Master Data) — layar kelola admin, digerbangi `BookPolicy` + permission `books.*`.

Pemisahan ini mencegah permission `books.view` (untuk admin) tercampur dengan hak akses baca katalog dasar yang semua orang punya.
