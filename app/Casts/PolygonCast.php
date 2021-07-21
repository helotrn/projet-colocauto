<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MStaack\LaravelPostgis\Geometries\LineString;
use MStaack\LaravelPostgis\Geometries\Point;
use MStaack\LaravelPostgis\Geometries\Polygon;

// Because of
// https://stackoverflow.com/questions/7309121/preferred-order-of-writing-latitude-longitude-tuples
class PolygonCast implements CastsAttributes
{
    public function set($model, $key, $value, $attributes)
    {
        if (is_array($value)) {
            // Don't try to create a polygon from an empty array.
            if (empty($value)) {
                return null;
            }

            $lineString = new LineString(
                array_map(function ($point) {
                    [$latitude, $longitude] = $point;
                    return new Point($latitude, $longitude);
                }, $value)
            );
            return new Polygon([$lineString]);
        }

        if (is_null($value) || is_string($value)) {
            return $value;
        }
        throw new \Exception("invalid"); // TODO
    }

    public function get($model, $key, $value, $attributes)
    {
        $polygon = $value;

        if (!$polygon) {
            return null;
        }

        if (is_array($polygon)) {
            return $polygon;
        }

        if (is_string($polygon)) {
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
