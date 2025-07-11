<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Brick\Money\Money;

class Cash implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // default everything to GBP for now
        // far in the future, work out some way to support multiple currencies
        // probably by calling an exchange rate API to addyour various balances together
        // and convert to GBP
        // todo: this^
        return Money::ofMinor($value, 'GBP');
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value instanceof Money) {
            return $value;
        }

        return $value->getMinorAmount()->toInt();
    }
}
