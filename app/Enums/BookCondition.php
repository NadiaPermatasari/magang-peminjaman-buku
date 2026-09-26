<?php

namespace App\Enums;

enum BookCondition: string
{
    case GOOD = 'GOOD';
    case MINOR_DAMAGE = 'MINOR_DAMAGE';
    case MAJOR_DAMAGE = 'MAJOR_DAMAGE';
    case LOST = 'LOST';

    public function label(): string
    {
        return match ($this) {
            self::GOOD => 'Baik',
            self::MINOR_DAMAGE => 'Rusak Ringan',
            self::MAJOR_DAMAGE => 'Rusak Berat',
            self::LOST => 'Hilang',
        };
    }
}
