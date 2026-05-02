<?php

namespace App\Enums;

enum DebtType: int
{
    case STANDARD = 0;
    case SPLIT_EVEN = 1;

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::SPLIT_EVEN => 'Split Even',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::STANDARD => 'bg-green-200',
            self::SPLIT_EVEN => 'bg-cyan-200',
        };
    }
}
