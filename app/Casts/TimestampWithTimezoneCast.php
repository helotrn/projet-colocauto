<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TimestampWithTimezoneCast implements CastsAttributes
{
    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }

    public function get($model, $key, $value, $attributes)
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->format("Y-m-d H:i:s");
    }
}
