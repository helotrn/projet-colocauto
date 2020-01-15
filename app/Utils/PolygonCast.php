<?php

namespace App\Utils;

use Vkovic\LaravelCustomCasts\CustomCastBase;

// Because of https://stackoverflow.com/questions/7309121/preferred-order-of-writing-latitude-longitude-tuples
class PolygonCast extends CustomCastBase
{
    public function setAttribute($polygon) {
        throw new \Exception('invalid'); // TODO
    }

    public function castAttribute($polygon) {
        $points = [];

        foreach ($polygon->getLineStrings() as $lineString) {
            foreach ($lineString->getPoints() as $point) {
                $latitude = $point->getLat();
                $longitude = $point->getLng();
                $points[] = [$latitude, $longitude];
            }
        }

        return $points;
    }
}
