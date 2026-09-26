<?php

namespace App\Enums;

enum FineStatus: string
{
    case UNPAID = 'UNPAID';
    case PAID = 'PAID';
    case WAIVED = 'WAIVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Dibayar',
            self::PAID => 'Lunas',
            self::WAIVED => 'Dibebaskan',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::UNPAID => 'from-red-600 to-orange-600',
            self::PAID => 'from-emerald-500 to-teal-400',
            self::WAIVED => 'from-slate-600 to-slate-300',
            self::CANCELLED => 'from-slate-600 to-slate-300',
        };
    }
}
