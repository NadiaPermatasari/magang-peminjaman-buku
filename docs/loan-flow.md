# Alur Peminjaman, Pengembalian & Denda

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
        │ scan barcode per eksemplar                          │  via scheduler)
        │ (HandoverLoan)                                      ▼
        ▼                                                  EXPIRED (selesai,
     BORROWED ──── melewati due_at ────┐                    eksemplar dilepas)
        │                              │ (MarkOverdueLoans,
        │ scan barcode saat kembali    │  via scheduler)
        │ (ReturnLoan → CalculateFine) ▼
        │                          OVERDUE
        │                              │
        │◄─────── scan barcode saat kembali (ReturnLoan) ─────┘
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
| `HandoverLoan` / `ReturnLoan` | `BookCopy::where('barcode', …)->lockForUpdate()` sebelum transisi status. |
| Pembuatan kode peminjaman | Baris counter harian di `loan_number_sequences` di-`lockForUpdate()` di dalam transaksi (lihat `docs/architecture.md`). |

## Eligibilitas Peminjaman (`CreateLoan`, spec §14)

Ditolak (`LoanException`, ditangkap controller → flash error) bila:

- Anggota tidak `ACTIVE` atau `expired_at` telah lewat (`Member::isActive()`)
- Jumlah item aktif (PENDING/APPROVED/BORROWED/OVERDUE) + permintaan baru melebihi `max_active_loans`
- `block_if_overdue` aktif **dan** anggota punya peminjaman berstatus OVERDUE
- `block_if_unpaid_fine` aktif **dan** anggota punya denda UNPAID
- Salah satu buku tidak aktif atau tidak punya eksemplar berstatus AVAILABLE saat pengajuan (pengecekan awal — alokasi sebenarnya baru terjadi saat approve)

## Denda (`CalculateFine`, spec §16/§18)

```
late_days = due_at → returned_at (hari, floor)
billable_days = late_days − fine_grace_period
amount = billable_days × fine_amount_per_day, dibatasi maximum_fine bila diisi
```

Tidak ada denda dibuat bila `fine_enabled = false`, `late_days ≤ fine_grace_period`, atau `amount ≤ 0`. Konfigurasi selalu dibaca dari `settings` (cached), **tidak pernah** hardcode.

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
