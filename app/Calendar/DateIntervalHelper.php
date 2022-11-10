<?php

namespace App\Calendar;

use Carbon\Carbon;

/*
  Intervals are always defined as [ , ).
*/
class DateIntervalHelper
{
    public static function isEmpty($interval)
    {
        if (!$interval || $interval[1]->lessThanOrEqualTo($interval[0])) {
            return true;
        }

        return false;
    }

    /**
     * Consider a series of intervals, this function removes the segments
     * overlapping with a given interval and return the resulting series of
     * intervals.
     *
     * @param fromIntervals
     *     Array of intervals from which to remove the given interval.
     *
     * @param toRemove
     *     Interval to remove.
     */
    public static function filterEmpty($fromIntervals)
    {
        $intervals = [];
        foreach ($fromIntervals as $interval) {
            if (!self::isEmpty($interval)) {
                $intervals[] = $interval;
            }
        }

        return $intervals;
    }

    /**
     * Consider a series of intervals, this function returns the intersection
     * with given interval overlapping with a given interval and return the
     * resulting series of intervals.
     *
     * Remarks:
     *   - This is a somewhat naive implementation.
     *
     * @param fromIntervals
     *     Array of intervals from which to remove the given interval.
     *
     * @param interval
     *     Interval with which to intersect
     */
    public static function Intersection($fromIntervals, $interval)
    {
        if (self::isEmpty($interval)) {
            return [];
        }

        $intervals = [];

        foreach ($fromIntervals as $fromInterval) {
            if (self::isEmpty($fromInterval)) {
                continue;
            }

            // 1. Interval to remove starts before
            // 5. Interval to remove ends after
            if (
                $interval[1]->lessThanOrEqualTo($fromInterval[0]) ||
                $interval[0]->greaterThanOrEqualTo($fromInterval[1])
            ) {
                continue;
            }

            // 2. Interval intersects at the beginning
            if (
                $interval[0]->lessThanOrEqualTo($fromInterval[0]) &&
                $interval[1]->greaterThan($fromInterval[0]) &&
                $interval[1]->lessThan($fromInterval[1])
            ) {
                $intervals[] = [$fromInterval[0], $interval[1]];
                continue;
            }

            // 3. Interval is included.
            if (
                $interval[0]->greaterThan($fromInterval[0]) &&
                $interval[1]->lessThan($fromInterval[1])
            ) {
                $intervals[] = $interval;
                continue;
            }

            // 4. Interval intersects at the end
            if (
                $interval[0]->greaterThan($fromInterval[0]) &&
                $interval[0]->lessThan($fromInterval[1]) &&
                $interval[1]->greaterThanOrEqualTo($fromInterval[1])
            ) {
                $intervals[] = [$interval[0], $fromInterval[1]];
                continue;
            }

            // 6. Interval to remove includes from interval
            if (
                $interval[0]->lessThanOrEqualTo($fromInterval[0]) &&
                $interval[1]->greaterThanOrEqualTo($fromInterval[1])
            ) {
                $intervals[] = $fromInterval;
                continue;
            }
        }

        return $intervals;
    }

    public static function hasIntersection($fromIntervals, $interval)
    {
        return !empty(self::Intersection($fromIntervals, $interval));
    }

    /**
     * Consider a series of intervals, this function returns the union with the
     * given interval, merging any joined or overlapping intervals.
     *
     * Remarks:
     *   - This is a somewhat naive implementation.
     *
     * @param fromIntervals
     *     Array of intervals to which to add the given interval.
     *
     * @param interval
     *     Interval to add to the current intervals.
     */
    public static function Union($fromIntervals, $interval)
    {
        if (!$fromIntervals) {
            $fromIntervals = [];
        }

        if (self::isEmpty($interval)) {
            return self::filterEmpty($fromIntervals);
        }

        // Starting from the given interval, the strategy is to add intervals
        // from fromIntervals by merging any joinining or overlapping interval
        // along the way.
        $intervals = [$interval];

        foreach ($fromIntervals as $fromInterval) {
            if (self::isEmpty($fromInterval)) {
                continue;
            }

            $unionInterval = $fromInterval;

            // Find intersecting or joining intervals to create one united interval.
            $intersectingKeys = [];
            foreach ($intervals as $key => $interval) {
                if (
                    self::hasIntersection([$fromInterval], $interval) ||
                    $interval[0]->equalTo($fromInterval[1]) ||
                    $interval[1]->equalTo($fromInterval[0])
                ) {
                    if ($interval[0]->lessThan($unionInterval[0])) {
                        $unionInterval[0] = $interval[0];
                    }
                    if ($interval[1]->greaterThan($unionInterval[1])) {
                        $unionInterval[1] = $interval[1];
                    }

                    $intersectingKeys[] = $key;
                }
            }

            // Remove intersecting intervals to replace them by the united interval.
            foreach ($intersectingKeys as $key) {
                unset($intervals[$key]);
            }

            $intervals[] = $unionInterval;
        }

        return $intervals;
    }

    /**
     * Consider a series of intervals, this function removes the segments
     * overlapping with a given interval and return the resulting series of
     * intervals.
     *
     * Remarks:
     *   - This is a somewhat naive implementation.
     *
     * @param fromIntervals
     *     Array of intervals from which to remove the given interval.
     *
     * @param toRemove
     *     Interval to remove.
     */
    public static function removeInterval($fromIntervals, $toRemove)
    {
        if (!$fromIntervals || empty($fromIntervals)) {
            return [];
        }

        if (self::isEmpty($toRemove)) {
            // Filter empty intervals before leaving.
            return self::filterEmpty($fromIntervals);
        }

        $intervals = [];

        foreach ($fromIntervals as $fromInterval) {
            if (self::isEmpty($fromInterval)) {
                continue;
            }

            // 1. Interval to remove starts before
            // 5. Interval to remove ends after
            if (
                $toRemove[1]->lessThanOrEqualTo($fromInterval[0]) ||
                $toRemove[0]->greaterThanOrEqualTo($fromInterval[1])
            ) {
                $intervals[] = $fromInterval;
                continue;
            }

            // 2. Interval intersects at the beginning
            if (
                $toRemove[0]->lessThanOrEqualTo($fromInterval[0]) &&
                $toRemove[1]->greaterThan($fromInterval[0]) &&
                $toRemove[1]->lessThan($fromInterval[1])
            ) {
                $intervals[] = [$toRemove[1], $fromInterval[1]];
                continue;
            }

            // 3. Interval to remove is included. Expect two intervals
            if (
                $toRemove[0]->greaterThan($fromInterval[0]) &&
                $toRemove[1]->lessThan($fromInterval[1])
            ) {
                $intervals[] = [$fromInterval[0], $toRemove[0]];
                $intervals[] = [$toRemove[1], $fromInterval[1]];
                continue;
            }

            // 4. Interval intersects at the end
            if (
                $toRemove[0]->greaterThan($fromInterval[0]) &&
                $toRemove[0]->lessThan($fromInterval[1]) &&
                $toRemove[1]->greaterThanOrEqualTo($fromInterval[1])
            ) {
                $intervals[] = [$fromInterval[0], $toRemove[0]];
                continue;
            }

            // 6. Interval to remove includes from interval
            if (
                $toRemove[0]->lessThanOrEqualTo($fromInterval[0]) &&
                $toRemove[1]->greaterThanOrEqualTo($fromInterval[1])
            ) {
                // Nothing to add.
                continue;
            }
        }

        return $intervals;
    }

    /*
     * Returns whether intervals cover a given interval
     *
     * Will chew intervals from interval to cover and return whether the result
     * is empty at the end.
     *
     * @param intervals
     *     Array of intervals that are expected to cover given interval.
     *
     * @param toRemove
     *     Interval to cover.
     */
    public static function cover($intervals, $toCover)
    {
        $toCover = [$toCover];

        foreach ($intervals as $interval) {
            $toCover = self::removeInterval($toCover, $interval);
        }

        return empty($toCover);
    }
}
