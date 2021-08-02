<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MStaack\LaravelPostgis\Geometries\Point;

// Because of
// https://stackoverflow.com/questions/7309121/preferred-order-of-writing-latitude-longitude-tuples
class PointCast implements CastsAttributes
{
    public function set($model, $key, $value, $attributes)
    {
        $point = $value;

        if (null === $value) {
            return null;
        }

        if (
            is_array($point) &&
            isset($point["latitude"]) &&
            isset($point["longitude"])
        ) {
            $latitude = $point["latitude"];
            $longitude = $point["longitude"];
        } elseif (
            is_array($point) &&
            count(array_filter($point, "is_numeric")) === 2
        ) {
            [$latitude, $longitude] = $point;
        } elseif ($point instanceof Point) {
            return $point;
        } elseif (
            is_string($point) &&
            preg_match("/-?\d+(?:\.\d+)?[, ] *-?\d+(?:\.\d+)?/", $point)
        ) {
            /* '/\d+/'
                                    Accept an integer number
                                '/\d+\.\d+/'
                                    Decimal number with mandatory decimals
                                '/\d+(?:\.\d+)?/'
                                    Optional decimals
                                '/-?\d+(?:\.\d+)?/'
                                    Accept negative numbers
                                '/-?\d+(?:\.\d+)?[, ]-?\d+(?:\.\d+)/?'
                                    Two numbers, separated by a comma or a space.
                                    Accept any number of spaces after separator.
                             */
            [$latitude, $longitude] = preg_split("/[, ] */", $point);
        } else {
            throw new \Exception("invalid");
        }

        return new Point($latitude, $longitude);
    }

    public function get($model, $key, $value, $attributes)
    {
        if (null === $value) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $latitude = $value->getLat();
        $longitude = $value->getLng();

        return [$latitude, $longitude];
    }
}
