<?php

namespace App\Utils;

use Phaza\LaravelPostgis\Geometries\Point;
use Vkovic\LaravelCustomCasts\CustomCastBase;

// Because of https://stackoverflow.com/questions/7309121/preferred-order-of-writing-latitude-longitude-tuples
class PointCast extends CustomCastBase
{
    public function setAttribute($point) {
        if (is_array($point) && isset($point['latitude']) && isset($point['longitude'])) {
            $latitude = $point['latitude'];
            $longitude = $point['longitude'];
        } elseif (is_array($point) && count(array_filter($point, 'is_numeric')) === 2) {
            [$latitude, $longitude] = $point;
        } elseif ($point instanceof Point) {
            return $point;
        } elseif (is_string($point) && preg_match('/-?\d+\.\d+[, ]-?\d+.\d+/', $point)) {
            [$latitude, $longitude] = preg_split('/[, ]/', $point);
        } else {
            throw new \Exception('invalid');
        }

        return new Point($latitude, $longitude);
    }

    public function castAttribute($point) {
        if ($point) {
            $latitude = $point->getLat();
            $longitude = $point->getLng();
            return [$latitude, $longitude];
        }
    }
}
