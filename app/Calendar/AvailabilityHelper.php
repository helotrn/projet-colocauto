<?php

namespace App\Calendar;

use Carbon\Carbon;

class AvailabilityHelper
{
    /*
     * Parse period string of the form 12:34-23:45 to arrays of integers
     * representing time. Accounts for expressions with or without seconds.
     * Will define them if not.
     * Will correct 23:59 end time to 24:00 to comply with the [ , ) interval
     * convention.
     */
    public static function ruleParsePeriodStr($periodStr)
    {
        [$startTime, $endTime] = explode("-", $periodStr);

        // Explode and convert to integers.
        $startTime = array_map(function ($s) {
            return intval($s);
        }, explode(":", $startTime));
        $endTime = array_map(function ($s) {
            return intval($s);
        }, explode(":", $endTime));

        // Set seconds if not set.
        $startTime[2] = isset($startTime[2]) ? $startTime[2] : 0;

        // Set seconds if not set.
        $endTime[2] = isset($endTime[2]) ? $endTime[2] : 0;

        // Enforce [, ) interval convention for the end of the day.
        if (23 == $endTime[0] && 59 == $endTime[1]) {
            $endTime = [24, 0, 0];
        }

        // Account for the exception 00:00:00-00:00:00 to be interpreted as
        // full day.
        if (
            0 == $startTime[0] &&
            0 == $startTime[1] &&
            0 == $startTime[2] &&
            0 == $endTime[0] &&
            0 == $endTime[1] &&
            0 == $endTime[2]
        ) {
            $endTime = [24, 0, 0];
        }

        return [$startTime, $endTime];
    }

    /*
     * @param rule
     *   Array containing:
     *     - type: "dates"
     *     - scope:
     *           An array of individual unordered dates.
     *     - [period]:
     *           Optional string defining availability or unavailability times.
     *
     * @param dateRange
     *   Date interval following the [, ) interval convention.
     */
    public static function ruleGetDatesIntervals($rule, $dateRange = null)
    {
        if (isset($rule["period"])) {
            $periodInterval = self::ruleParsePeriodStr($rule["period"]);
        } else {
            $periodInterval = [[0, 0, 0], [24, 0, 0]];
        }

        // Set time to 0 to ensure consistency with the fact that we expect dates.
        if ($dateRange) {
            $dateRange[0] = $dateRange[0]->copy()->setTime(0, 0, 0);
            $dateRange[1] = $dateRange[1]->copy()->setTime(0, 0, 0);
        }

        $intervals = [];
        foreach ($rule["scope"] as $dateStr) {
            $currentDate = new Carbon($dateStr);

            $interval = [
                $currentDate->copy()->setTime(0, 0, 0),
                $currentDate->copy()->setTime(24, 0, 0),
            ];

            if (
                !$dateRange ||
                DateIntervalHelper::hasIntersection([$interval], $dateRange)
            ) {
                // setTime gracefully accounts for time = 24:00:00
                // and will set to 00:00:00 on the next day :)
                $intervals[] = [
                    $currentDate
                        ->copy()
                        ->setTime(
                            $periodInterval[0][0],
                            $periodInterval[0][1],
                            $periodInterval[0][2]
                        ),
                    $currentDate
                        ->copy()
                        ->setTime(
                            $periodInterval[1][0],
                            $periodInterval[1][1],
                            $periodInterval[1][2]
                        ),
                ];
            }
        }

        return $intervals;
    }

    /*
     * @param rule
     *   Array containing:
     *     - type: "dateRange"
     *     - scope:
     *           An array of dates from which the first and the last represent the endpoints of the range.
     *           This is a [, ] interval
     *     - [period]:
     *           Optional string defining availability or unavailability times.
     *
     * @param dateRange
     *   Date interval following the [, ) interval convention.
     */
    public static function ruleGetDateRangeIntervals($rule, $dateRange = null)
    {
        if (isset($rule["period"])) {
            $periodInterval = self::ruleParsePeriodStr($rule["period"]);
        } else {
            $periodInterval = [[0, 0, 0], [24, 0, 0]];
        }

        // Get first and last days of interval no matter the
        // format of scope (list of all dates or start and end date).
        // Assume they are in order.
        $ruleRange = [null, null];
        foreach ($rule["scope"] as $dateStr) {
            if (!$ruleRange[0]) {
                $ruleRange[0] = $dateStr;
            }
            $ruleRange[1] = $dateStr;
        }

        $ruleRange[0] = (new \Carbon\Carbon($ruleRange[0]))->setTime(0, 0, 0);
        $ruleRange[1] = (new \Carbon\Carbon($ruleRange[1]))->setTime(24, 0, 0);

        // Prepare range.
        if ($dateRange) {
            // Set time to 0 to ensure consistency with the fact that we expect dates.
            $dateRange[0] = $dateRange[0]->copy()->setTime(0, 0, 0);
            $dateRange[1] = $dateRange[1]->copy()->setTime(0, 0, 0);

            // Intersection of the two ranges so as to have the least number of days to check.
            $dateRange = DateIntervalHelper::intersection(
                [$dateRange],
                $ruleRange
            );

            // If no intersection, then no interval.
            if (empty($dateRange)) {
                return [];
            }

            // Expect an array of one interval.
            $dateRange = $dateRange[0];
        } else {
            $dateRange = $ruleRange;
        }

        $currentDate = $dateRange[0]->copy();
        $intervals = [];
        while ($currentDate->lessThan($dateRange[1])) {
            $intervals[] = [
                $currentDate
                    ->copy()
                    ->setTime(
                        $periodInterval[0][0],
                        $periodInterval[0][1],
                        $periodInterval[0][2]
                    ),
                $currentDate
                    ->copy()
                    ->setTime(
                        $periodInterval[1][0],
                        $periodInterval[1][1],
                        $periodInterval[1][2]
                    ),
            ];

            $currentDate->addDay();
        }

        return $intervals;
    }

    /*
     * @param rule
     *   Array containing:
     *     - type: "weekdays"
     *     - scope:
     *           Weekdays on which the rule applies.
     *     - [period]:
     *           Optional string defining availability or unavailability times.
     *
     * @param dateRange
     *   Date interval following the [, ) interval convention.
     */
    public static function ruleGetWeekdaysIntervals($rule, $dateRange)
    {
        static $isoWeekdays = [
            1 => "MO",
            2 => "TU",
            3 => "WE",
            4 => "TH",
            5 => "FR",
            6 => "SA",
            7 => "SU",
        ];

        if (isset($rule["period"])) {
            $periodInterval = self::ruleParsePeriodStr($rule["period"]);
        } else {
            $periodInterval = [[0, 0, 0], [24, 0, 0]];
        }

        // Set time to 0 to ensure consistency with the fact that we expect dates.
        $dateRange[0] = $dateRange[0]->copy()->setTime(0, 0, 0);
        $dateRange[1] = $dateRange[1]->copy()->setTime(0, 0, 0);

        $currentDate = $dateRange[0]->copy();
        $intervals = [];
        while ($currentDate->lessThan($dateRange[1])) {
            if (
                in_array(
                    $isoWeekdays[$currentDate->isoWeekday()],
                    $rule["scope"]
                )
            ) {
                $intervals[] = [
                    $currentDate
                        ->copy()
                        ->setTime(
                            $periodInterval[0][0],
                            $periodInterval[0][1],
                            $periodInterval[0][2]
                        ),
                    $currentDate
                        ->copy()
                        ->setTime(
                            $periodInterval[1][0],
                            $periodInterval[1][1],
                            $periodInterval[1][2]
                        ),
                ];
            }

            $currentDate->addDay();
        }

        return $intervals;
    }

    /*
     * For all availability rules, will generate daily intervals over a period
     * given by dateRange.
     * Daily means that any interval such as those returned by date ranges will
     * be split into individual days.
     *
     * @param availabilityParams
     *     available: boolean indicating the default availability.
     *     rules: Exceptions to the default availability.
     */
    public static function getScheduleDailyIntervals(
        $availabilityParams,
        $dateRange
    ) {
        // Set time to 0 to ensure consistency with the fact that we expect dates.
        $dateRange[0] = $dateRange[0]->copy()->setTime(0, 0, 0);
        $dateRange[1] = $dateRange[1]->copy()->setTime(0, 0, 0);

        $dailyIntervals = [];
        $ruleIntervals = [];

        // Get availability or unavailability intervals.
        foreach ($availabilityParams["rules"] as $rule) {
            switch ($rule["type"]) {
                case "dates":
                    $ruleIntervals = self::ruleGetDatesIntervals(
                        $rule,
                        $dateRange
                    );
                    break;

                case "dateRange":
                    $ruleIntervals = self::ruleGetDateRangeIntervals(
                        $rule,
                        $dateRange
                    );

                    // Split date ranges into individual days.
                    $currentDate = $dateRange[0]->copy();
                    $intervals = [];
                    while ($currentDate->lessThan($dateRange[1])) {
                        $dateInterval = [
                            $currentDate->copy()->setTime(0, 0, 0),
                            $currentDate->copy()->setTime(24, 0, 0),
                        ];

                        // There should be only one interval per day.
                        $interval = DateIntervalHelper::Intersection(
                            $ruleIntervals,
                            $dateInterval
                        );
                        if (count($interval) > 1) {
                            throw new \Exception("Only one interval expected.");
                        }

                        if ($interval) {
                            $intervals[] = $interval[0];
                        }

                        $currentDate->addDay();
                    }

                    $ruleIntervals = $intervals;
                    break;

                case "weekdays":
                    $ruleIntervals = self::ruleGetWeekdaysIntervals(
                        $rule,
                        $dateRange
                    );
                    break;
            }

            $dailyIntervals = array_merge($dailyIntervals, $ruleIntervals);
        }

        return $dailyIntervals;
    }

    /**
     * This method checks whether the loanable is available based on the
     * availability schedule.
     *
     * @param availabilityParams
     *     available: boolean indicating the default availability.
     *     rules: Exceptions to the default availability.
     */
    public static function isScheduleAvailable(
        $availabilityParams,
        $loanInterval
    ) {
        $loanDateRange[0] = $loanInterval[0]->copy()->setTime(0, 0, 0);

        // If loan ends at 00:00:00, then don't go to the next day.
        if (
            $loanInterval[1]->hour == 0 &&
            $loanInterval[1]->minute == 0 &&
            $loanInterval[1]->second == 0
        ) {
            $loanDateRange[1] = $loanInterval[1]->copy()->setTime(0, 0, 0);
        } else {
            $loanDateRange[1] = $loanInterval[1]->copy()->setTime(24, 0, 0);
        }

        // Get availability or unavailability intervals.
        foreach ($availabilityParams["rules"] as $rule) {
            $ruleIntervals = [];

            switch ($rule["type"]) {
                case "dates":
                    $ruleIntervals = AvailabilityHelper::ruleGetDatesIntervals(
                        $rule,
                        $loanDateRange
                    );
                    break;

                case "dateRange":
                    $ruleIntervals = AvailabilityHelper::ruleGetDateRangeIntervals(
                        $rule,
                        $loanDateRange
                    );
                    break;

                case "weekdays":
                    $ruleIntervals = AvailabilityHelper::ruleGetWeekdaysIntervals(
                        $rule,
                        $loanDateRange
                    );
                    break;
            }

            if ($availabilityParams["available"] == true) {
                // If intervals intersect with loanInterval, then loanable is unavailable
                if (
                    DateIntervalHelper::hasIntersection(
                        $ruleIntervals,
                        $loanInterval
                    )
                ) {
                    return false;
                }
            } else {
                // If intervals cover loanInterval, then loanable is available
                if (DateIntervalHelper::cover($ruleIntervals, $loanInterval)) {
                    return true;
                }
            }
        }

        return $availabilityParams["available"];
    }
}
