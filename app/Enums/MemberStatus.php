<?php

namespace App\Enums;

enum MemberStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case SUSPENDED = 'SUSPENDED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::SUSPENDED => 'Ditangguhkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'from-emerald-500 to-teal-400',
            self::INACTIVE => 'from-slate-600 to-slate-300',
            self::SUSPENDED => 'from-red-600 to-orange-600',
        };
    }
}
