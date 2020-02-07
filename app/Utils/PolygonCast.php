<?php

namespace App\Utils;

use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Polygon;
use Vkovic\LaravelCustomCasts\CustomCastBase;

// Because of https://stackoverflow.com/questions/7309121/preferred-order-of-writing-latitude-longitude-tuples
class PolygonCast extends CustomCastBase
{
    public function setAttribute($value) {
        if (is_array($value)) {
            $lineString = new LineString(
                array_map(function ($point) {
                    [$latitude, $longitude] = $point;
                    return new Point($latitude, $longitude);
                }, $value)
            );
            return new Polygon([$lineString]);
        }

        if (is_null($value)) {
            return $value;
        }

        throw new \Exception('invalid'); // TODO
    }

    public function castAttribute($polygon) {
        if (!$polygon) {
            return null;
        }

        if (is_array($polygon)) {
            return $polygon;
        }

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
