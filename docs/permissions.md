# Role & Permission

Didefinisikan di `database/seeders/RolePermissionSeeder.php` (idempotent — aman dijalankan ulang). Otorisasi runtime **selalu** lewat pemeriksaan permission (`$user->can('...')`) atau Policy, tidak pernah `if ($user->role === '...')` (spec §4/§69).

## Role

| Role | Ringkasan |
|---|---|
| `super-admin` | Seluruh permission. Satu-satunya yang bisa mengelola User/Role/Permission, Pengaturan (termasuk keamanan), dan melihat Audit Log/Security Dashboard penuh. |
| `admin-perpustakaan` | Kelola penuh master data (buku, eksemplar, kategori, rak), anggota beserta akun loginnya, verifikasi & kelola seluruh transaksi peminjaman/pengembalian/perpanjangan, laporan. |
| `petugas` | Lihat master data, ajukan peminjaman atas nama anggota, verifikasi peminjaman, penyerahan (handover), pengembalian, putuskan perpanjangan. Tidak bisa ubah master data. |
| `anggota` | Lihat katalog, ajukan/batalkan peminjaman sendiri, ajukan perpanjangan, lihat riwayatnya sendiri. |
| `pimpinan` | Read-only — dashboard & laporan saja. |

## Katalog Permission

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

## Menambah Permission Baru

1. Tambahkan nama permission ke `RolePermissionSeeder::PERMISSIONS`.
2. Tambahkan ke role yang relevan di `RolePermissionSeeder::ROLE_PERMISSIONS` (super-admin otomatis dapat semua).
3. Jalankan `php artisan db:seed --class=RolePermissionSeeder`.
4. Gerbangi route (`permission:nama.permission` middleware dan/atau di Policy) dan render menu (`@can('nama.permission')` di `resources/views/layouts/partials/sidenav.blade.php`).

## Object-Level Authorization (IDOR)

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
