<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Uppercase implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return mb_strtoupper($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        return mb_strtoupper($value);
    }
}
