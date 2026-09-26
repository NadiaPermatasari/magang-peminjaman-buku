# Role & Permission

Didefinisikan di `database/seeders/RolePermissionSeeder.php` (idempotent — aman dijalankan ulang). Otorisasi runtime **selalu** lewat pemeriksaan permission (`$user->can('...')`) atau Policy, tidak pernah `if ($user->role === '...')` (spec §4/§69).

## Role

| Role | Ringkasan |
|---|---|
| `super-admin` | Seluruh permission. Satu-satunya yang bisa mengelola User/Role/Permission, Pengaturan (termasuk keamanan), dan melihat Audit Log/Security Dashboard penuh. |
| `admin-perpustakaan` | Kelola penuh master data (buku, eksemplar, kategori, penulis, penerbit, rak), anggota, verifikasi & kelola seluruh transaksi peminjaman/pengembalian/denda (kecuali *waive*), laporan. |
| `petugas` | Lihat master data, verifikasi peminjaman, penyerahan (handover), pengembalian, tandai denda lunas. Tidak bisa ubah master data. |
| `anggota` | Lihat katalog, ajukan/batalkan peminjaman sendiri, lihat riwayat & denda sendiri. |
| `pimpinan` | Read-only — dashboard & laporan saja. |

## Katalog Permission

```
dashboard.view

books.view / books.create / books.update / books.delete
book-copies.view / book-copies.create / book-copies.update / book-copies.delete
categories.view / categories.create / categories.update / categories.delete
authors.view / authors.create / authors.update / authors.delete
publishers.view / publishers.create / publishers.update / publishers.delete
racks.view / racks.create / racks.update / racks.delete

members.view / members.create / members.update / members.delete

loans.create / loans.view-own / loans.view-all / loans.approve /
loans.reject / loans.handover / loans.cancel

returns.process

fines.view-own / fines.view / fines.mark-paid / fines.waive

reports.view / reports.export

users.view / users.create / users.update / users.disable
roles.manage / permissions.manage
settings.manage
audit-logs.view
security-dashboard.view
```

`fines.waive` sengaja **tidak** diberikan ke `admin-perpustakaan` — pembebasan denda dibatasi hanya untuk `super-admin` sesuai default aman spec §58 ("fine waiver = restricted"). Beri secara eksplisit lewat layar Role bila instansi memang membutuhkannya.

## Menambah Permission Baru

1. Tambahkan nama permission ke `RolePermissionSeeder::PERMISSIONS`.
2. Tambahkan ke role yang relevan di `RolePermissionSeeder::ROLE_PERMISSIONS` (super-admin otomatis dapat semua).
3. Jalankan `php artisan db:seed --class=RolePermissionSeeder`.
4. Gerbangi route (`permission:nama.permission` middleware dan/atau di Policy) dan render menu (`@can('nama.permission')` di `resources/views/layouts/partials/sidenav.blade.php`).

## Object-Level Authorization (IDOR)

Permission saja tidak cukup untuk mencegah satu anggota melihat data anggota lain (spec §26). Setiap model yang punya "pemilik" (Loan, Fine, Member) menggunakan Policy dengan pola:

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
