<?php

namespace App\Enums;

enum LoanStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case BORROWED = 'BORROWED';
    case OVERDUE = 'OVERDUE';
    case RETURNED = 'RETURNED';
    case EXPIRED = 'EXPIRED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::BORROWED => 'Sedang Dipinjam',
            self::OVERDUE => 'Terlambat',
            self::RETURNED => 'Selesai',
            self::EXPIRED => 'Kedaluwarsa',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'from-orange-500 to-yellow-500',
            self::APPROVED => 'from-cyan-500 to-blue-500',
            self::REJECTED => 'from-red-600 to-orange-600',
            self::BORROWED => 'from-blue-500 to-violet-500',
            self::OVERDUE => 'from-red-600 to-orange-600',
            self::RETURNED => 'from-emerald-500 to-teal-400',
            self::EXPIRED => 'from-slate-600 to-slate-300',
            self::CANCELLED => 'from-slate-600 to-slate-300',
        };
    }

    /**
     * Legal transitions (spec §47 — e.g. REJECTED -> BORROWED must never
     * happen). Enforced centrally by the Loan Action classes.
     *
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::PENDING => [self::APPROVED, self::REJECTED, self::CANCELLED],
            self::APPROVED => [self::BORROWED, self::EXPIRED, self::CANCELLED],
            self::BORROWED => [self::RETURNED, self::OVERDUE],
            // OVERDUE -> BORROWED hanya terjadi lewat perpanjangan yang
            // disetujui (ApproveLoanExtension menggeser jatuh tempo).
            self::OVERDUE => [self::RETURNED, self::BORROWED],
            self::REJECTED, self::RETURNED, self::EXPIRED, self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->transitions(), true);
    }
}
