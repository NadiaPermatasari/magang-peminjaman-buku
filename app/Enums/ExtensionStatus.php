<?php

namespace App\Enums;

enum ExtensionStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'from-orange-500 to-yellow-500',
            self::APPROVED => 'from-emerald-500 to-teal-400',
            self::REJECTED => 'from-red-600 to-orange-600',
        };
    }
}
