<?php

namespace App\Enums;

enum DebtType: int
{
    case Standard = 0;
    case SplitEven = 1;

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::SplitEven => 'Split Even',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Standard => 'bg-gray-100 text-gray-800',
            self::SplitEven => 'bg-blue-100 text-blue-800',
        };
    }
}
