# Alur Peminjaman, Pengembalian & Perpanjangan

## Diagram Status

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

## Titik Kunci Konkurensi (spec §40)

| Aksi | Lock |
|---|---|
| `ApproveLoan` | `BookCopy::where('book_id', …)->where('status', AVAILABLE)->lockForUpdate()` — dua approval bersamaan untuk buku yang sama tidak bisa mengambil eksemplar yang sama (dites di `LoanFlowTest::test_a_book_copy_cannot_be_allocated_to_two_loans_at_once`). |
| `HandoverLoan` | `BookCopy::where('barcode', …)->lockForUpdate()` sebelum transisi status. |
| `ReturnLoan` | `LoanItem::whereKey(…)->lockForUpdate()` lalu validasi ulang statusnya di dalam transaksi — dua petugas tidak bisa memproses pengembalian item yang sama. |
| `RequestLoanExtension` | Pengajuan PENDING milik peminjaman yang sama di-`lockForUpdate()` — tidak bisa ada dua pengajuan menunggu sekaligus. |
| Pembuatan kode peminjaman | Baris counter harian di `loan_number_sequences` di-`lockForUpdate()` di dalam transaksi (lihat `docs/architecture.md`). |

## Eligibilitas Peminjaman (`CreateLoan`, spec §14)

Ditolak (`LoanException`, ditangkap controller → flash error) bila:

- Anggota tidak `ACTIVE` atau `expired_at` telah lewat (`Member::isActive()`)
- Jumlah item aktif (PENDING/APPROVED/BORROWED/OVERDUE) + permintaan baru melebihi `max_active_loans`
- `block_if_overdue` aktif **dan** anggota punya peminjaman berstatus OVERDUE
- Salah satu buku tidak aktif atau tidak punya eksemplar berstatus AVAILABLE saat pengajuan (pengecekan awal — alokasi sebenarnya baru terjadi saat approve)

Petugas/admin (`loans.view-all`) yang akunnya tidak terhubung ke data anggota mengajukan **atas nama** anggota: `member_id` wajib diisi di form Pengajuan. Anggota tidak pernah bisa memakai field itu untuk mengajukan atas nama orang lain — `LoanController::store` selalu memakai `member` milik akunnya sendiri bila ada.

## Perpanjangan / Banding (`RequestLoanExtension`, `ApproveLoanExtension`, `RejectLoanExtension`)

Anggota mengajukan tambahan hari dari halaman detail peminjaman; petugas/admin (`loan-extensions.approve`) menyetujui atau menolak dari menu **Transaksi > Perpanjangan**.

Pengajuan ditolak sistem (`LoanException`) bila: pengaturan `allow_renewal` nonaktif, `max_renewals` sudah tercapai (atau bernilai 0), peminjaman tidak berstatus BORROWED/OVERDUE, atau masih ada pengajuan PENDING untuk peminjaman yang sama.

Saat disetujui:

```
base      = due_at bila masih di masa depan, selain itu now()
new_due_at = base + days
```

`due_at` peminjaman **dan** seluruh item yang belum kembali digeser ke `new_due_at`. Peminjaman OVERDUE kembali ke BORROWED (satu-satunya transisi OVERDUE → BORROWED yang legal di `LoanStatus::transitions()`). Penolakan tidak mengubah jatuh tempo dan wajib menyertakan alasan, yang dikirimkan ke anggota.

## Bukti Foto (pengganti scan barcode)

- **Serah terima** — `loan_items.handover_photo_path`, diunggah saat HandoverLoan (opsional) atau menyusul dari halaman **Peminjaman Aktif**.
- **Pengembalian** — `loan_items.return_photo_path`, **wajib**: petugas memilih eksemplar dari daftar peminjaman aktif lalu mengunggah fotonya, tidak ada lagi input barcode.

Keduanya disimpan di disk `public` (`storage/app/public/loan-proofs`), divalidasi sebagai gambar (MIME dibaca dari isi file, maks 4 MB) dan lewat rate limiter `upload`.

## Kondisi Eksemplar Saat Kembali

| Kondisi dipilih petugas | Status eksemplar setelahnya |
|---|---|
| `GOOD` | `AVAILABLE` |
| `MINOR_DAMAGE` | `MAINTENANCE` |
| `MAJOR_DAMAGE` | `DAMAGED` |
| `LOST` | `LOST` |

## Reminder (spec §19/§20)

Command `loans:process-reminders` (dijadwalkan tiap jam) mengirim tiga jenis notifikasi berdasarkan pengaturan Notifikasi:

- **Pickup** — `pickup_reminder_hours` sebelum `pickup_deadline` (loan APPROVED)
- **Due** — H- sesuai daftar `due_reminder_days` (mis. `[3,1,0]`) dari `due_at` (loan BORROWED)
- **Overdue** — hari-ke sesuai daftar `overdue_reminder_days` (mis. `[1,3,7]`) setelah `due_at` terlewati (loan OVERDUE)

Setiap kombinasi `(loan, type, milestone)` dicatat di tabel `loan_reminders` (unique constraint) agar command aman dijalankan berulang tanpa mengirim reminder yang sama dua kali.
