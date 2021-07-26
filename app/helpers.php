<?php

function wrap_array_keys($value)
{
    if (!is_array($value)) {
        return [$value];
    }
    return array_keys($value);
}

// https://github.com/gregallensworth/PHP-Geometry
/**
 * PHP-Geometry
 * ============
 *
 * Various PHP code for processing geometry. This is largely developed to suit our use
 * cases, and is not a concerted effort to provide a comprehensive set of functionality
 * (GEOS in pure PHP?). But you're welcome to it.
 *
 * -GA
 */
function get_area_of_polygon($geometry)
{
    if (!$geometry) {
        return 0;
    }

    $area = 0;
    for ($vi = 0, $vl = sizeof($geometry); $vi < $vl; $vi++) {
        $thisx = $geometry[$vi][0];
        $thisy = $geometry[$vi][1];
        $nextx = $geometry[($vi + 1) % $vl][0];
        $nexty = $geometry[($vi + 1) % $vl][1];
        $area += $thisx * $nexty - $thisy * $nextx;
    }
    $area = abs($area / 2);
    return $area;
}

function get_centroid_of_polygon($geometry)
{
    if (!$geometry) {
        return 0;
    }

    $cx = 0;
    $cy = 0;

    for ($vi = 0, $vl = sizeof($geometry); $vi < $vl; $vi++) {
        $thisx = $geometry[$vi][0];
        $thisy = $geometry[$vi][1];
        $nextx = $geometry[($vi + 1) % $vl][0];
        $nexty = $geometry[($vi + 1) % $vl][1];

        $p = $thisx * $nexty - $thisy * $nextx;
        $cx += ($thisx + $nextx) * $p;
        $cy += ($thisy + $nexty) * $p;
    }

    // last step of centroid: divide by 6*A
    $area = get_area_of_polygon($geometry);
    $cx = -$cx / (6 * $area);
    $cy = -$cy / (6 * $area);

    return [$cx, $cy];
}
