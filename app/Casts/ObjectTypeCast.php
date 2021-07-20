<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ObjectTypeCast implements CastsAttributes
{
    public function set($model, $key, $value, $attributes)
    {
        switch ($value) {
            case "car":
                return "App\Models\Car";
            case "trailer":
                return "App\Models\Trailer";
            case "bike":
                return "App\Models\Bike";
            default:
                return $value;
        }
    }

    public function get($model, $key, $value, $attributes)
    {
        switch ($value) {
            case "App\Models\Car":
                return "car";
            case "App\Models\Trailer":
                return "trailer";
            case "App\Models\Bike":
                return "bike";
            default:
                return $value;
        }
    }
}
