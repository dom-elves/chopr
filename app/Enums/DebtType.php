<?php

namespace App\Enums;

enum DebtType: int
{
    case false = 0;
    case true = 1;

    public function label(): string
    {
        return match ($this) {
            self::false => 'Standard',
            self::true => 'Split Even',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::false => 'bg-gray-100 text-gray-800',
            self::true => 'bg-blue-100 text-blue-800',
        };
    }
}
