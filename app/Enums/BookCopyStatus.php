<?php

namespace App\Enums;

enum BookCopyStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case RESERVED = 'RESERVED';
    case BORROWED = 'BORROWED';
    case MAINTENANCE = 'MAINTENANCE';
    case DAMAGED = 'DAMAGED';
    case LOST = 'LOST';
    case INACTIVE = 'INACTIVE';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Tersedia',
            self::RESERVED => 'Direservasi',
            self::BORROWED => 'Dipinjam',
            self::MAINTENANCE => 'Perawatan',
            self::DAMAGED => 'Rusak',
            self::LOST => 'Hilang',
            self::INACTIVE => 'Nonaktif',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::AVAILABLE => 'from-emerald-500 to-teal-400',
            self::RESERVED => 'from-orange-500 to-yellow-500',
            self::BORROWED => 'from-blue-500 to-violet-500',
            self::MAINTENANCE => 'from-cyan-500 to-blue-500',
            self::DAMAGED, self::LOST => 'from-red-600 to-orange-600',
            self::INACTIVE => 'from-slate-600 to-slate-300',
        };
    }

    /**
     * Legal transitions for the automated loan flow (spec §47 — e.g.
     * REJECTED -> BORROWED must never happen). Manual inventory edits by
     * staff (book-copies.update) are not restricted by this map; it is
     * enforced by the Loan/Return action classes.
     *
     * @return list<self>
     */
    public function loanFlowTransitions(): array
    {
        return match ($this) {
            self::AVAILABLE => [self::RESERVED, self::MAINTENANCE, self::INACTIVE],
            self::RESERVED => [self::BORROWED, self::AVAILABLE],
            self::BORROWED => [self::AVAILABLE, self::DAMAGED, self::MAINTENANCE, self::LOST],
            self::MAINTENANCE => [self::AVAILABLE, self::INACTIVE, self::LOST],
            self::DAMAGED, self::LOST, self::INACTIVE => [self::AVAILABLE, self::MAINTENANCE],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->loanFlowTransitions(), true);
    }
}
