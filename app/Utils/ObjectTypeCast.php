<?php

namespace App\Utils;

use Vkovic\LaravelCustomCasts\CustomCastBase;

class ObjectTypeCast extends CustomCastBase
{
    public function setAttribute($objectType) {
        switch ($objectType) {
            case 'car':
                return 'App\Models\Car';
            case 'trailer':
                return 'App\Models\Trailer';
            case 'bike':
                return 'App\Models\Bike';
            default:
                return null;
        }
    }

    public function castAttribute($objectType) {
        switch ($objectType) {
            case 'App\Models\Car':
                return 'car';
            case 'App\Models\Trailer':
                return 'trailer';
            case 'App\Models\Bike':
                return 'bike';
            default:
                return null;
        }
    }
}
