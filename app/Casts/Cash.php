<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Brick\Money\Money;

/**
 * Affected: debt->amount, share->amount, group_user->balance.
 */
class Cash implements CastsAttributes
{
    /**
     * Cast the given value to a money object when accessed.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // return Money::ofMinor($value, 'GBP');
        return $value;
    }

    /**
     * Prepare the given value for storage by changing it to minor units.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // if (!$value instanceof Money) {
        //     return $value;
        // }

        // return $value->getMinorAmount()->toInt();
        return $value;
    }
}
