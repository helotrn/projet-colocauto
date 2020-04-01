<?php

namespace App\Utils;

use Carbon\Carbon;
use Vkovic\LaravelCustomCasts\CustomCastBase;

class TimestampWithTimezone extends CustomCastBase
{
    public function setAttribute($value) {
        return $value;
    }

    public function castAttribute($value) {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
