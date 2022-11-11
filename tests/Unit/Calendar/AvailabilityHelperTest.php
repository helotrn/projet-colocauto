<?php

namespace Tests\Unit\Calendar;

use App\Calendar\AvailabilityHelper;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class AvailabilityHelperTest extends TestCase
{
    use AssertsIntervals;

    public function testRuleParsePeriodStr()
    {
        // Without seconds
        $period = AvailabilityHelper::ruleParsePeriodStr("12:34-23:45");
        $expected = [[12, 34, 0], [23, 45, 0]];
        $this->assertEquals($expected, $period);

        // Last minute exception
        $period = AvailabilityHelper::ruleParsePeriodStr("23:59-23:59");
        $expected = [[23, 59, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = AvailabilityHelper::ruleParsePeriodStr("23:59-24:00");
        $expected = [[23, 59, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        // 00:00-00:00 exception
        $period = AvailabilityHelper::ruleParsePeriodStr("00:00-00:00");
        $expected = [[0, 0, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        // With seconds
        $period = AvailabilityHelper::ruleParsePeriodStr("12:34:56-23:45:01");
        $expected = [[12, 34, 56], [23, 45, 1]];
        $this->assertEquals($expected, $period);

        // Last minute exception
        $period = AvailabilityHelper::ruleParsePeriodStr("23:59:00-23:59:00");
        $expected = [[23, 59, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = AvailabilityHelper::ruleParsePeriodStr("23:59:05-23:59:05");
        $expected = [[23, 59, 5], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = AvailabilityHelper::ruleParsePeriodStr("23:59:59-23:59:59");
        $expected = [[23, 59, 59], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = AvailabilityHelper::ruleParsePeriodStr("23:59:59-24:00:00");
        $expected = [[23, 59, 59], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        // 00:00:00-00:00:00 exception
        $period = AvailabilityHelper::ruleParsePeriodStr("00:00:00-00:00:00");
        $expected = [[0, 0, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);
    }

    public function testRuleGetDatesIntervals()
    {
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDatesIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
            [
                new Carbon("2021-11-12 00:00:00"),
                new Carbon("2021-11-13 00:00:00"),
            ],
            [
                new Carbon("2021-11-13 00:00:00"),
                new Carbon("2021-11-14 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Test partial intersection
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-09"), new Carbon("2021-11-13")];

        $intervals = AvailabilityHelper::ruleGetDatesIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
            [
                new Carbon("2021-11-12 00:00:00"),
                new Carbon("2021-11-13 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Test intersection with last day
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-13"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDatesIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-13 00:00:00"),
                new Carbon("2021-11-14 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Test no intersection
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-14"), new Carbon("2021-11-18")];

        $intervals = AvailabilityHelper::ruleGetDatesIntervals(
            $rule,
            $dateRange
        );
        $expected = [];
        $this->assertSameIntervals($expected, $intervals);

        // Test without period.
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDatesIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
            [
                new Carbon("2021-11-12 00:00:00"),
                new Carbon("2021-11-13 00:00:00"),
            ],
            [
                new Carbon("2021-11-13 00:00:00"),
                new Carbon("2021-11-14 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Set period
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
            "period" => "10:30-18:45",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDatesIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 10:30:00"),
                new Carbon("2021-11-07 18:45:00"),
            ],
            [
                new Carbon("2021-11-09 10:30:00"),
                new Carbon("2021-11-09 18:45:00"),
            ],
            [
                new Carbon("2021-11-12 10:30:00"),
                new Carbon("2021-11-12 18:45:00"),
            ],
            [
                new Carbon("2021-11-13 10:30:00"),
                new Carbon("2021-11-13 18:45:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Undefined date range
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
            "period" => "00:00-24:00",
        ];

        $intervals = AvailabilityHelper::ruleGetDatesIntervals($rule);
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
            [
                new Carbon("2021-11-12 00:00:00"),
                new Carbon("2021-11-13 00:00:00"),
            ],
            [
                new Carbon("2021-11-13 00:00:00"),
                new Carbon("2021-11-14 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);
    }

    public function testRuleGetDateRangeIntervals()
    {
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-08", "2021-11-09"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-08 00:00:00"),
                new Carbon("2021-11-09 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // test same day, repeated.
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-07"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Test same day, one date in scope.
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Test partial intersection
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-14"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-04"), new Carbon("2021-11-09")];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-08 00:00:00"),
                new Carbon("2021-11-09 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Test no intersection
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-14"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-02"), new Carbon("2021-11-07")];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals(
            $rule,
            $dateRange
        );
        $expected = [];
        $this->assertSameIntervals($expected, $intervals);

        // Test without period.
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-08", "2021-11-09"],
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-08 00:00:00"),
                new Carbon("2021-11-09 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Set period
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-08", "2021-11-09"],
            "period" => "10:30-18:45",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 10:30:00"),
                new Carbon("2021-11-07 18:45:00"),
            ],
            [
                new Carbon("2021-11-08 10:30:00"),
                new Carbon("2021-11-08 18:45:00"),
            ],
            [
                new Carbon("2021-11-09 10:30:00"),
                new Carbon("2021-11-09 18:45:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Undefined date range
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-08", "2021-11-09"],
            "period" => "00:00-24:00",
        ];

        $intervals = AvailabilityHelper::ruleGetDateRangeIntervals($rule);
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-08 00:00:00"),
                new Carbon("2021-11-09 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);
    }

    public function testRuleGetWeekdaysIntervals()
    {
        $rule = [
            "type" => "weekdays",
            "scope" => ["SU", "WE", "SA"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetWeekdaysIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 00:00:00"),
                new Carbon("2021-11-08 00:00:00"),
            ],
            [
                new Carbon("2021-11-10 00:00:00"),
                new Carbon("2021-11-11 00:00:00"),
            ],
            [
                new Carbon("2021-11-13 00:00:00"),
                new Carbon("2021-11-14 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Other weekdays, unordered
        $rule = [
            "type" => "weekdays",
            "scope" => ["FR", "TH", "MO", "TU"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetWeekdaysIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-08 00:00:00"),
                new Carbon("2021-11-09 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
            [
                new Carbon("2021-11-11 00:00:00"),
                new Carbon("2021-11-12 00:00:00"),
            ],
            [
                new Carbon("2021-11-12 00:00:00"),
                new Carbon("2021-11-13 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Test without period.
        $rule = [
            "type" => "weekdays",
            "scope" => ["MO", "TU"],
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetWeekdaysIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-08 00:00:00"),
                new Carbon("2021-11-09 00:00:00"),
            ],
            [
                new Carbon("2021-11-09 00:00:00"),
                new Carbon("2021-11-10 00:00:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Set period
        $rule = [
            "type" => "weekdays",
            "scope" => ["SU", "WE", "SA"],
            "period" => "10:30-18:45",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = AvailabilityHelper::ruleGetWeekdaysIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-07 10:30:00"),
                new Carbon("2021-11-07 18:45:00"),
            ],
            [
                new Carbon("2021-11-10 10:30:00"),
                new Carbon("2021-11-10 18:45:00"),
            ],
            [
                new Carbon("2021-11-13 10:30:00"),
                new Carbon("2021-11-13 18:45:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);

        // Longer date range
        $rule = [
            "type" => "weekdays",
            "scope" => ["MO", "TH"],
            "period" => "10:30-18:45",
        ];
        $dateRange = [new Carbon("2021-11-08"), new Carbon("2021-11-23")];

        $intervals = AvailabilityHelper::ruleGetWeekdaysIntervals(
            $rule,
            $dateRange
        );
        $expected = [
            [
                new Carbon("2021-11-08 10:30:00"),
                new Carbon("2021-11-08 18:45:00"),
            ],
            [
                new Carbon("2021-11-11 10:30:00"),
                new Carbon("2021-11-11 18:45:00"),
            ],
            [
                new Carbon("2021-11-15 10:30:00"),
                new Carbon("2021-11-15 18:45:00"),
            ],
            [
                new Carbon("2021-11-18 10:30:00"),
                new Carbon("2021-11-18 18:45:00"),
            ],
            [
                new Carbon("2021-11-22 10:30:00"),
                new Carbon("2021-11-22 18:45:00"),
            ],
        ];
        $this->assertSameIntervals($expected, $intervals);
    }

    public function testgetScheduleDailyIntervals()
    {
        $rules = [
            [
                "available" => true,
                "type" => "dates",
                "scope" => ["2021-12-07", "2021-12-09"],
                "period" => "11:00-13:00",
            ],
            [
                "available" => true,
                "type" => "dateRange",
                "scope" => ["2021-12-14", "2021-12-16"],
                "period" => "17:00-21:00",
            ],
            [
                "available" => true,
                "type" => "dateRange",
                "scope" => ["2021-12-22", "2021-12-24"],
            ],
            [
                "available" => true,
                "type" => "weekdays",
                "scope" => ["SA"],
                "period" => "12:00-13:00",
            ],
            [
                "available" => true,
                "type" => "weekdays",
                "scope" => ["MO", "SU"],
                "period" => "00:00-23:59",
            ],
        ];

        $dateRange = [new Carbon("2021-12-01"), new Carbon("2022-01-01")];
        $intervals = AvailabilityHelper::getScheduleDailyIntervals(
            ["rules" => $rules],
            $dateRange
        );

        $expected = [
            // Dates
            [
                new Carbon("2021-12-07 11:00:00"),
                new Carbon("2021-12-07 13:00:00"),
            ],
            [
                new Carbon("2021-12-09 11:00:00"),
                new Carbon("2021-12-09 13:00:00"),
            ],
            // Date range, 17:00-21:00
            [
                new Carbon("2021-12-14 17:00:00"),
                new Carbon("2021-12-14 21:00:00"),
            ],
            [
                new Carbon("2021-12-15 17:00:00"),
                new Carbon("2021-12-15 21:00:00"),
            ],
            [
                new Carbon("2021-12-16 17:00:00"),
                new Carbon("2021-12-16 21:00:00"),
            ],
            // Date range, all day
            [
                new Carbon("2021-12-22 00:00:00"),
                new Carbon("2021-12-23 00:00:00"),
            ],
            [
                new Carbon("2021-12-23 00:00:00"),
                new Carbon("2021-12-24 00:00:00"),
            ],
            [
                new Carbon("2021-12-24 00:00:00"),
                new Carbon("2021-12-25 00:00:00"),
            ],
            // Saturdays, 12:00-13:00
            [
                new Carbon("2021-12-04 12:00:00"),
                new Carbon("2021-12-04 13:00:00"),
            ],
            [
                new Carbon("2021-12-11 12:00:00"),
                new Carbon("2021-12-11 13:00:00"),
            ],
            [
                new Carbon("2021-12-18 12:00:00"),
                new Carbon("2021-12-18 13:00:00"),
            ],
            [
                new Carbon("2021-12-25 12:00:00"),
                new Carbon("2021-12-25 13:00:00"),
            ],
            // Sundays and Mondays, all day
            [
                new Carbon("2021-12-05 00:00:00"),
                new Carbon("2021-12-06 00:00:00"),
            ],
            [
                new Carbon("2021-12-06 00:00:00"),
                new Carbon("2021-12-07 00:00:00"),
            ],
            [
                new Carbon("2021-12-12 00:00:00"),
                new Carbon("2021-12-13 00:00:00"),
            ],
            [
                new Carbon("2021-12-13 00:00:00"),
                new Carbon("2021-12-14 00:00:00"),
            ],
            [
                new Carbon("2021-12-19 00:00:00"),
                new Carbon("2021-12-20 00:00:00"),
            ],
            [
                new Carbon("2021-12-20 00:00:00"),
                new Carbon("2021-12-21 00:00:00"),
            ],
            [
                new Carbon("2021-12-26 00:00:00"),
                new Carbon("2021-12-27 00:00:00"),
            ],
            [
                new Carbon("2021-12-27 00:00:00"),
                new Carbon("2021-12-28 00:00:00"),
            ],
        ];

        $this->assertSameIntervals($expected, $intervals);
    }

    public function testgetScheduleDailyIntervals_ignoresInvalidTypes()
    {
        $rules = [
            [
                "available" => true,
                "type" => null,
                "scope" => [],
                "period" => "0:00-24:00",
            ],
        ];

        $dateRange = [new Carbon("2021-12-01"), new Carbon("2022-01-01")];

        // This doesn't throw an exception
        $intervals = AvailabilityHelper::getScheduleDailyIntervals(
            ["rules" => $rules],
            $dateRange
        );

        $expected = [];

        $this->assertSameIntervals($expected, $intervals);
    }

    public function testGetDailyAvailability_UnvailableByDefaultReturnAvailable()
    {
        $dateRange = [
            new CarbonImmutable("2022-11-06 00:00:00"),
            new CarbonImmutable("2022-11-13 00:00:00"),
        ];

        $availableByDefault = false;
        $returnAvailable = true;

        // Start with a case with no rules at all.
        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => [],
            ],
            $dateRange,
            $returnAvailable
        );

        $this->assertSameIntervals([], $availability);

        // Then add rules.
        $availabilityRules = [
            // Sunday: No rule
            // Monday: One rule
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-07"],
                "period" => "11:00-13:00",
            ],
            // Tuesday: Adjacent rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "09:00-11:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "11:00-13:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "15:00-18:00",
            ],
            // Wednesday: Overlapping rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "09:00-12:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "11:00-19:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "15:00-18:00",
            ],
            // Saturday: Whole day
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-12"],
                "period" => "00:00-24:00",
            ],
        ];

        // availability opartout
        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => $availabilityRules,
            ],
            $dateRange,
            $returnAvailable
        );

        $expected = [
            // Sunday: No rule
            // Monday: One rule
            [
                new CarbonImmutable("2022-11-07 11:00:00"),
                new CarbonImmutable("2022-11-07 13:00:00"),
            ],
            // Tuesday: Adjacent rules
            [
                new CarbonImmutable("2022-11-08 09:00:00"),
                new CarbonImmutable("2022-11-08 13:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 15:00:00"),
                new CarbonImmutable("2022-11-08 18:00:00"),
            ],
            // Wednesday: Overlapping rules
            [
                new CarbonImmutable("2022-11-09 09:00:00"),
                new CarbonImmutable("2022-11-09 19:00:00"),
            ],
            // Saturday: Whole day
            [
                new CarbonImmutable("2022-11-12 00:00:00"),
                new CarbonImmutable("2022-11-12 24:00:00"),
            ],
        ];

        $this->assertSameIntervals($expected, $availability);
    }

    public function testGetDailyAvailability_AvailableByDefaultReturnAvailable()
    {
        $dateRange = [
            new CarbonImmutable("2022-11-06 00:00:00"),
            new CarbonImmutable("2022-11-13 00:00:00"),
        ];

        $availableByDefault = true;
        $returnAvailable = true;

        // Start with a case with no rules at all.
        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => [],
            ],
            $dateRange,
            $returnAvailable
        );

        $expected = [
            [
                new CarbonImmutable("2022-11-06 00:00:00"),
                new CarbonImmutable("2022-11-06 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-07 00:00:00"),
                new CarbonImmutable("2022-11-07 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 00:00:00"),
                new CarbonImmutable("2022-11-08 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-09 00:00:00"),
                new CarbonImmutable("2022-11-09 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-10 00:00:00"),
                new CarbonImmutable("2022-11-10 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-11 00:00:00"),
                new CarbonImmutable("2022-11-11 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-12 00:00:00"),
                new CarbonImmutable("2022-11-12 24:00:00"),
            ],
        ];

        $this->assertSameIntervals($expected, $availability);

        // Then add rules.
        $availabilityRules = [
            // Sunday: No rule
            // Monday: One rule
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-07"],
                "period" => "11:00-13:00",
            ],
            // Tuesday: Adjacent rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "09:00-11:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "11:00-13:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "15:00-18:00",
            ],
            // Wednesday: Overlapping rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "09:00-12:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "11:00-19:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "15:00-18:00",
            ],
            // Saturday: Whole day
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-12"],
                "period" => "00:00-24:00",
            ],
        ];

        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => $availabilityRules,
            ],
            $dateRange,
            $returnAvailable
        );

        $expected = [
            // Sunday: No rule
            [
                new CarbonImmutable("2022-11-06 00:00:00"),
                new CarbonImmutable("2022-11-06 24:00:00"),
            ],
            // Monday: One rule
            [
                new CarbonImmutable("2022-11-07 00:00:00"),
                new CarbonImmutable("2022-11-07 11:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-07 13:00:00"),
                new CarbonImmutable("2022-11-07 24:00:00"),
            ],
            // Tuesday: Adjacent rules
            [
                new CarbonImmutable("2022-11-08 00:00:00"),
                new CarbonImmutable("2022-11-08 09:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 13:00:00"),
                new CarbonImmutable("2022-11-08 15:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 18:00:00"),
                new CarbonImmutable("2022-11-08 24:00:00"),
            ],
            // Wednesday: Overlapping rules
            [
                new CarbonImmutable("2022-11-09 00:00:00"),
                new CarbonImmutable("2022-11-09 09:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-09 19:00:00"),
                new CarbonImmutable("2022-11-09 24:00:00"),
            ],
            // Thursday: No rule
            [
                new CarbonImmutable("2022-11-10 00:00:00"),
                new CarbonImmutable("2022-11-10 24:00:00"),
            ],
            // Friday: No rule
            [
                new CarbonImmutable("2022-11-11 00:00:00"),
                new CarbonImmutable("2022-11-11 24:00:00"),
            ],
            // Saturday: Whole day
        ];

        $this->assertSameIntervals($expected, $availability);
    }

    public function testGetDailyAvailability_UnavailableByDefaultReturnUnavailable()
    {
        $dateRange = [
            new CarbonImmutable("2022-11-06 00:00:00"),
            new CarbonImmutable("2022-11-13 00:00:00"),
        ];

        $availableByDefault = false;
        $returnAvailable = false;

        // Start with a case with no rules at all.
        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => [],
            ],
            $dateRange,
            $returnAvailable
        );

        $expected = [
            [
                new CarbonImmutable("2022-11-06 00:00:00"),
                new CarbonImmutable("2022-11-06 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-07 00:00:00"),
                new CarbonImmutable("2022-11-07 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 00:00:00"),
                new CarbonImmutable("2022-11-08 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-09 00:00:00"),
                new CarbonImmutable("2022-11-09 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-10 00:00:00"),
                new CarbonImmutable("2022-11-10 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-11 00:00:00"),
                new CarbonImmutable("2022-11-11 24:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-12 00:00:00"),
                new CarbonImmutable("2022-11-12 24:00:00"),
            ],
        ];

        $this->assertSameIntervals($expected, $availability);

        // Then add rules.
        $availabilityRules = [
            // Sunday: No rule
            // Monday: One rule
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-07"],
                "period" => "11:00-13:00",
            ],
            // Tuesday: Adjacent rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "09:00-11:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "11:00-13:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "15:00-18:00",
            ],
            // Wednesday: Overlapping rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "09:00-12:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "11:00-19:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "15:00-18:00",
            ],
            // Saturday: Whole day
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-12"],
                "period" => "00:00-24:00",
            ],
        ];

        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => $availabilityRules,
            ],
            $dateRange,
            $returnAvailable
        );

        $expected = [
            // Sunday: No rule
            [
                new CarbonImmutable("2022-11-06 00:00:00"),
                new CarbonImmutable("2022-11-06 24:00:00"),
            ],
            // Monday: One rule
            [
                new CarbonImmutable("2022-11-07 00:00:00"),
                new CarbonImmutable("2022-11-07 11:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-07 13:00:00"),
                new CarbonImmutable("2022-11-07 24:00:00"),
            ],
            // Tuesday: Adjacent rules
            [
                new CarbonImmutable("2022-11-08 00:00:00"),
                new CarbonImmutable("2022-11-08 09:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 13:00:00"),
                new CarbonImmutable("2022-11-08 15:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 18:00:00"),
                new CarbonImmutable("2022-11-08 24:00:00"),
            ],
            // Wednesday: Overlapping rules
            [
                new CarbonImmutable("2022-11-09 00:00:00"),
                new CarbonImmutable("2022-11-09 09:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-09 19:00:00"),
                new CarbonImmutable("2022-11-09 24:00:00"),
            ],
            // Thursday: No rule
            [
                new CarbonImmutable("2022-11-10 00:00:00"),
                new CarbonImmutable("2022-11-10 24:00:00"),
            ],
            // Friday: No rule
            [
                new CarbonImmutable("2022-11-11 00:00:00"),
                new CarbonImmutable("2022-11-11 24:00:00"),
            ],
            // Saturday: Whole day
        ];

        $this->assertSameIntervals($expected, $availability);
    }

    public function testGetDailyAvailability_AvailableByDefaultReturnUnavailable()
    {
        $dateRange = [
            new CarbonImmutable("2022-11-06 00:00:00"),
            new CarbonImmutable("2022-11-13 00:00:00"),
        ];

        $availableByDefault = true;
        $returnAvailable = false;

        // Start with a case with no rules at all.
        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => [],
            ],
            $dateRange,
            $returnAvailable
        );

        $this->assertSameIntervals([], $availability);

        // Then add rules.
        $availabilityRules = [
            // Sunday: No rule
            // Monday: One rule
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-07"],
                "period" => "11:00-13:00",
            ],
            // Tuesday: Adjacent rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "09:00-11:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "11:00-13:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-08"],
                "period" => "15:00-18:00",
            ],
            // Wednesday: Overlapping rules
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "09:00-12:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "11:00-19:00",
            ],
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-09"],
                "period" => "15:00-18:00",
            ],
            // Saturday: Whole day
            [
                "available" => !$availableByDefault,
                "type" => "dates",
                "scope" => ["2022-11-12"],
                "period" => "00:00-24:00",
            ],
        ];

        $availability = AvailabilityHelper::getDailyAvailability(
            [
                "available" => $availableByDefault,
                "rules" => $availabilityRules,
            ],
            $dateRange,
            $returnAvailable
        );

        $expected = [
            // Sunday: No rule
            // Monday: One rule
            [
                new CarbonImmutable("2022-11-07 11:00:00"),
                new CarbonImmutable("2022-11-07 13:00:00"),
            ],
            // Tuesday: Adjacent rules
            [
                new CarbonImmutable("2022-11-08 09:00:00"),
                new CarbonImmutable("2022-11-08 13:00:00"),
            ],
            [
                new CarbonImmutable("2022-11-08 15:00:00"),
                new CarbonImmutable("2022-11-08 18:00:00"),
            ],
            // Wednesday: Overlapping rules
            [
                new CarbonImmutable("2022-11-09 09:00:00"),
                new CarbonImmutable("2022-11-09 19:00:00"),
            ],
            // Saturday: Whole day
            [
                new CarbonImmutable("2022-11-12 00:00:00"),
                new CarbonImmutable("2022-11-12 24:00:00"),
            ],
        ];

        $this->assertSameIntervals($expected, $availability);
    }

    public function testIsScheduleAvailable()
    {
        $available = false;
        $rules = [
            [
                "available" => true,
                "type" => "dates",
                "scope" => ["2021-12-07", "2021-12-09"],
                "period" => "11:00-13:00",
            ],
            [
                "available" => true,
                "type" => "dateRange",
                "scope" => ["2021-12-14", "2021-12-16"],
                "period" => "17:00-21:00",
            ],
            [
                "available" => true,
                "type" => "dateRange",
                "scope" => ["2021-12-22", "2021-12-24"],
            ],
            [
                "available" => true,
                "type" => "weekdays",
                "scope" => ["SA"],
                "period" => "12:00-13:00",
            ],
            [
                "available" => true,
                "type" => "weekdays",
                "scope" => ["MO", "SU"],
                "period" => "00:00-23:59",
            ],
        ];

        // "dates"
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-09 11:00:00"),
                    (new Carbon("2021-12-09 11:00:00"))->addMinutes(60),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-09 10:59:00"),
                    (new Carbon("2021-12-09 10:59:00"))->addMinutes(2),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-09 12:59:00"),
                    (new Carbon("2021-12-09 12:59:00"))->addMinutes(2),
                ]
            )
        );

        // "dateRange"
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-14 18:00:00"),
                    (new Carbon("2021-12-14 18:00:00"))->addMinutes(180),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-14 16:59:00"),
                    (new Carbon("2021-12-14 16:59:00"))->addMinutes(2),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-14 20:59:00"),
                    (new Carbon("2021-12-14 20:59:00"))->addMinutes(2),
                ]
            )
        );

        // Multi-day "dateRange"
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-22 00:00:00"),
                    (new Carbon("2021-12-22 00:00:00"))->addMinutes(72 * 60),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-21 23:59:00"),
                    (new Carbon("2021-12-21 23:59:00"))->addMinutes(2),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-24 23:59:00"),
                    (new Carbon("2021-12-24 23:59:00"))->addMinutes(2),
                ]
            )
        );

        // "weekdays"
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 12:00:00"),
                    (new Carbon("2021-12-18 12:00:00"))->addMinutes(60),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 11:59:00"),
                    (new Carbon("2021-12-18 11:59:00"))->addMinutes(2),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 12:59:00"),
                    (new Carbon("2021-12-18 12:59:00"))->addMinutes(2),
                ]
            )
        );

        // Multi-day "weekdays"
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-19 00:00:00"),
                    (new Carbon("2021-12-19 00:00:00"))->addMinutes(48 * 60),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 23:59:00"),
                    (new Carbon("2021-12-18 23:59:00"))->addMinutes(2),
                ]
            )
        );
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-20 23:59:00"),
                    (new Carbon("2021-12-20 23:59:00"))->addMinutes(2),
                ]
            )
        );

        // Match no rule.
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-07 13:00:00"),
                    (new Carbon("2021-12-07 13:00:00"))->addMinutes(15),
                ]
            )
        );

        $available = true;
        $rules = [
            [
                "available" => false,
                "type" => "dates",
                "scope" => ["2021-12-07", "2021-12-09"],
                "period" => "11:00-13:00",
            ],
            [
                "available" => false,
                "type" => "dateRange",
                "scope" => ["2021-12-14", "2021-12-16"],
                "period" => "17:00-21:00",
            ],
            [
                "available" => false,
                "type" => "dateRange",
                "scope" => ["2021-12-22", "2021-12-24"],
            ],
            [
                "available" => false,
                "type" => "weekdays",
                "scope" => ["SA"],
                "period" => "12:00-13:00",
            ],
            [
                "available" => false,
                "type" => "weekdays",
                "scope" => ["MO", "SU"],
                "period" => "00:00-23:59",
            ],
        ];

        // "dates"
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-09 11:00:00"),
                    (new Carbon("2021-12-09 11:00:00"))->addMinutes(120),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-09 10:30:00"),
                    (new Carbon("2021-12-09 10:30:00"))->addMinutes(30),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-09 13:00:00"),
                    (new Carbon("2021-12-09 13:00:00"))->addMinutes(30),
                ]
            )
        );

        // "dateRange"
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-14 17:00:00"),
                    (new Carbon("2021-12-14 17:00:00"))->addMinutes(240),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-14 16:30:00"),
                    (new Carbon("2021-12-14 16:30:00"))->addMinutes(30),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-14 21:00:00"),
                    (new Carbon("2021-12-14 21:00:00"))->addMinutes(30),
                ]
            )
        );

        // Multi-day "dateRange"
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-22 00:00:00"),
                    (new Carbon("2021-12-22 00:00:00"))->addMinutes(72 * 60),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-21 23:30:00"),
                    (new Carbon("2021-12-21 23:30:00"))->addMinutes(30),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-25 00:00:00"),
                    (new Carbon("2021-12-25 00:00:00"))->addMinutes(30),
                ]
            )
        );

        // "weekdays"
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 12:00:00"),
                    (new Carbon("2021-12-18 12:00:00"))->addMinutes(60),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 11:30:00"),
                    (new Carbon("2021-12-18 11:30:00"))->addMinutes(30),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 13:00:00"),
                    (new Carbon("2021-12-18 13:00:00"))->addMinutes(30),
                ]
            )
        );

        // Multi-day "weekdays"
        $this->assertFalse(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-19 00:00:00"),
                    (new Carbon("2021-12-19 00:00:00"))->addMinutes(48 * 60),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-18 23:30:00"),
                    (new Carbon("2021-12-18 23:30:00"))->addMinutes(30),
                ]
            )
        );
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-21 00:00:00"),
                    (new Carbon("2021-12-21 00:00:00"))->addMinutes(30),
                ]
            )
        );

        // No rule.
        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-07 13:00:00"),
                    (new Carbon("2021-12-07 13:00:00"))->addMinutes(15),
                ]
            )
        );
    }

    public function testIsScheduleAvailable_overMultipleRules()
    {
        $available = false;
        $rules = [
            [
                "available" => true,
                "type" => "dates",
                "scope" => ["2021-12-07"],
                "period" => "11:00-24:00",
            ],
            [
                "available" => true,
                "type" => "dates",
                "scope" => ["2021-12-08"],
                "period" => "00:00-13:00",
            ],
        ];

        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-07 11:00:00"),
                    new Carbon("2021-12-08 12:00:00"),
                ]
            )
        );

        $available = false;
        $rules = [
            [
                "available" => true,
                "type" => "dates",
                "scope" => ["2021-12-07"], // Tuesday
                "period" => "11:00-24:00",
            ],
            [
                "available" => true,
                "type" => "weekdays",
                "scope" => ["WE"],
                "period" => "00:00-13:00",
            ],
        ];

        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-07 11:00:00"),
                    new Carbon("2021-12-08 12:00:00"),
                ]
            )
        );

        $available = false;
        $rules = [
            [
                "available" => true,
                "type" => "dates",
                "scope" => ["2021-12-07"],
                "period" => "11:00-24:00",
            ],
            [
                "available" => true,
                "type" => "dateRange",
                "scope" => ["2021-12-08", "2021-12-09"],
                "period" => "00:00-24:00",
            ],
        ];

        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-07 11:00:00"),
                    new Carbon("2021-12-09 12:00:00"),
                ]
            )
        );

        $available = false;
        $rules = [
            [
                "available" => true,
                "type" => "weekdays",
                "scope" => ["TU"],
                "period" => "11:00-24:00",
            ],
            [
                "available" => true,
                "type" => "dateRange",
                "scope" => ["2021-12-08", "2021-12-09"],
                "period" => "00:00-24:00",
            ],
        ];

        $this->assertTrue(
            AvailabilityHelper::isScheduleAvailable(
                ["available" => $available, "rules" => $rules],
                [
                    new Carbon("2021-12-07 11:00:00"),
                    new Carbon("2021-12-09 12:00:00"),
                ]
            )
        );
    }
}
