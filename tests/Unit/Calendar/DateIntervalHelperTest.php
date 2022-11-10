<?php

namespace Tests\Unit\Calendar;

use App\Calendar\DateIntervalHelper;
use Carbon\Carbon;
use Tests\TestCase;

class DateIntervalHelperTest extends TestCase
{
    use AssertsIntervals;

    public function testIsEmpty()
    {
        $this->assertTrue(DateIntervalHelper::isEmpty(null));
        $this->assertTrue(DateIntervalHelper::isEmpty([]));

        // [a, a) is empty
        $interval = [
            new Carbon("2021-10-10 12:34:56"),
            new Carbon("2021-10-10 12:34:56"),
        ];
        $this->assertTrue(DateIntervalHelper::isEmpty($interval));

        // [a, <a) is empty
        $interval = [
            new Carbon("2021-10-10 12:34:56"),
            new Carbon("2021-10-10 12:34:55"),
        ];
        $this->assertTrue(DateIntervalHelper::isEmpty($interval));

        // [a, >a) is not empty
        $interval = [
            new Carbon("2021-10-10 12:34:56"),
            new Carbon("2021-10-10 12:34:57"),
        ];
        $this->assertFalse(DateIntervalHelper::isEmpty($interval));
    }

    public function testFilterEmpty()
    {
        $intervals = [
            null,
            [],
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-10 12:34:56"),
            ],
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-10 12:34:55"),
            ],
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-10 12:34:57"),
            ],
        ];

        $filtered = DateIntervalHelper::filterEmpty($intervals);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-10 12:34:57"),
            ],
        ];
        $this->assertSameIntervals($expected, $filtered);
    }

    public function testIntersection()
    {
        // 1. Interval starts before
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-10 12:34:56"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );
        $this->assertSameIntervals([], $intersection, "Interval starts before");

        // 2. Interval intersects at the beginning
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-13 12:34:56"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-13 12:34:56"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "Interval intersects at the beginning"
        );

        // 3. Interval is included.
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-13 23:45:01"),
            new Carbon("2021-10-17 12:34:56"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [
            [
                new Carbon("2021-10-13 23:45:01"),
                new Carbon("2021-10-17 12:34:56"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "Interval is included"
        );

        // 4. Interval intersects at the end
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-17 23:45:01"),
            new Carbon("2021-10-31 12:34:56"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [
            [
                new Carbon("2021-10-17 23:45:01"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "Interval intersects at the end"
        );

        // 5. Interval ends after
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-19 23:45:01"),
            new Carbon("2021-10-31 12:34:56"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );
        $this->assertSameIntervals([], $intersection, "Interval ends after");

        // 6. Interval includes from interval
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-08 23:45:01"),
            new Carbon("2021-10-22 12:34:56"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "Interval includes"
        );

        // 7. Intersection with empty interval (null)
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];

        $intersection = DateIntervalHelper::intersection($fromIntervals, null);

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "Empty interval (null)"
        );

        // 7. Intersect with empty interval (start = end)
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "Empty interval (start = end)"
        );

        // 8. From intervals is empty (empty, not empty)
        $fromIntervals = [
            [
                new Carbon("2021-10-15 23:45:01"),
                new Carbon("2021-10-15 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-13 23:45:01"),
            new Carbon("2021-10-17 12:34:56"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "From intervals empty interval (empty, not empty)"
        );

        // 8. From intervals is empty (null, not null)
        $fromIntervals = [null];
        $interval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "From intervals empty interval (null, not null)"
        );

        // 8. From intervals is empty (null, null)
        $fromIntervals = null;
        $interval = null;

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "From intervals empty interval ([], null)"
        );

        // 8. From intervals is empty ([], null)
        $fromIntervals = [];
        $interval = null;

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "From intervals empty interval ([], null)"
        );

        // 8. From intervals is empty ([null], null)
        $fromIntervals = [null];
        $interval = null;

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "From intervals empty interval ([null], null)"
        );

        // 8. From intervals is empty (empty, same empty)
        $fromIntervals = [
            [
                new Carbon("2021-10-15 23:45:01"),
                new Carbon("2021-10-15 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $intersection = DateIntervalHelper::intersection(
            $fromIntervals,
            $interval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intersection,
            "From intervals empty interval (empty, same empty)"
        );
    }

    public function testHasIntersection()
    {
        // Just a few tests as hasIntersection() uses Intersection(). Test more
        // thoroughly if this is to change.

        // 1. Interval starts before
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-10 12:34:56"),
        ];

        $hasIntersection = DateIntervalHelper::hasIntersection(
            $fromIntervals,
            $interval
        );
        $this->assertFalse($hasIntersection, "Interval starts before");

        // 2. Interval intersects at the beginning
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-13 12:34:56"),
        ];

        $hasIntersection = DateIntervalHelper::hasIntersection(
            $fromIntervals,
            $interval
        );

        $this->assertTrue(
            $hasIntersection,
            "Interval intersects at the beginning"
        );
    }

    public function testUnion()
    {
        // 1. Interval starts before
        $fromIntervals = [
            [
                new Carbon("2021-10-11 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-10 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-01 23:45:01"),
                new Carbon("2021-10-10 12:34:56"),
            ],
            [
                new Carbon("2021-10-11 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals($expected, $union, "Interval starts before");

        // 2. Interval intersects at the beginning
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-13 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-01 23:45:01"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];

        $this->assertSameIntervals(
            $expected,
            $union,
            "Interval intersects at the beginning"
        );

        // 2. Interval joins at the beginning
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-10 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-01 23:45:01"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];

        $this->assertSameIntervals(
            $expected,
            $union,
            "Interval intersects at the beginning"
        );

        // 3. Interval is included.
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-13 23:45:01"),
            new Carbon("2021-10-17 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals($expected, $union, "Interval is included");

        // 4. Interval intersects at the end
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-17 23:45:01"),
            new Carbon("2021-10-31 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-31 12:34:56"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $union,
            "Interval intersects at the end"
        );

        // 4. Interval joins at the end
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-19 23:45:01"),
            new Carbon("2021-10-31 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-31 12:34:56"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $union,
            "Interval intersects at the end"
        );

        // 5. Interval ends after
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-20 23:45:01"),
            new Carbon("2021-10-31 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
            [
                new Carbon("2021-10-20 23:45:01"),
                new Carbon("2021-10-31 12:34:56"),
            ],
        ];
        $this->assertSameIntervals($expected, $union, "Interval ends after");

        // 6. Interval includes from interval
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-08 23:45:01"),
            new Carbon("2021-10-22 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-08 23:45:01"),
                new Carbon("2021-10-22 12:34:56"),
            ],
        ];
        $this->assertSameIntervals($expected, $union, "Interval includes");

        // 7. union with empty interval (null)
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];

        $union = DateIntervalHelper::union($fromIntervals, null);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals($expected, $union, "Empty interval (null)");

        // 7. Intersect with empty interval (start = end)
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $union,
            "Empty interval (start = end)"
        );

        // 8. From intervals is empty (empty, not empty)
        $fromIntervals = [
            [
                new Carbon("2021-10-15 23:45:01"),
                new Carbon("2021-10-15 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-13 23:45:01"),
            new Carbon("2021-10-17 12:34:56"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [
            [
                new Carbon("2021-10-13 23:45:01"),
                new Carbon("2021-10-17 12:34:56"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $union,
            "From intervals empty interval (empty, not empty)"
        );

        // 8. From intervals is empty (null, not null)
        $fromIntervals = [null];
        $interval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $union,
            "From intervals empty interval (null, not null)"
        );

        // 8. From intervals is empty (null, null)
        $fromIntervals = null;
        $interval = null;

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $union,
            "From intervals empty interval ([], null)"
        );

        // 8. From intervals is empty ([], null)
        $fromIntervals = [];
        $interval = null;

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $union,
            "From intervals empty interval ([], null)"
        );

        // 8. From intervals is empty ([null], null)
        $fromIntervals = [null];
        $interval = null;

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $union,
            "From intervals empty interval ([null], null)"
        );

        // 8. From intervals is empty (empty, same empty)
        $fromIntervals = [
            [
                new Carbon("2021-10-15 23:45:01"),
                new Carbon("2021-10-15 23:45:01"),
            ],
        ];
        $interval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $union = DateIntervalHelper::union($fromIntervals, $interval);

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $union,
            "From intervals empty interval (empty, same empty)"
        );
    }

    public function testRemoveInterval()
    {
        // 1. Interval to remove starts before
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-10 12:34:56"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );
        $this->assertSameIntervals(
            $fromIntervals,
            $intervals,
            "Interval starts before"
        );

        // 2. Interval intersects at the beginning
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-01 23:45:01"),
            new Carbon("2021-10-13 12:34:56"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [
            [
                new Carbon("2021-10-13 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "Interval intersects at the beginning"
        );

        // 3. Interval to remove is included. Expect two intervals
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-13 23:45:01"),
            new Carbon("2021-10-17 12:34:56"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-13 23:45:01"),
            ],
            [
                new Carbon("2021-10-17 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "Interval is included"
        );

        // 4. Interval intersects at the end
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-17 23:45:01"),
            new Carbon("2021-10-31 12:34:56"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-17 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "Interval intersects at the end"
        );

        // 5. Interval to remove ends after
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-19 23:45:01"),
            new Carbon("2021-10-31 12:34:56"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );
        $this->assertSameIntervals(
            $fromIntervals,
            $intervals,
            "Interval ends after"
        );

        // 6. Interval to remove includes from interval
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-08 23:45:01"),
            new Carbon("2021-10-22 12:34:56"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [];
        $this->assertSameIntervals($expected, $intervals, "Interval includes");

        // 7. Remove empty interval (null)
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];

        $intervals = DateIntervalHelper::removeInterval($fromIntervals, null);

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "Empty interval (null)"
        );

        // 7. Remove empty interval (start = end)
        $fromIntervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "Empty interval (start = end)"
        );

        // 8. From intervals is empty (empty, not empty)
        $fromIntervals = [
            [
                new Carbon("2021-10-15 23:45:01"),
                new Carbon("2021-10-15 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-13 23:45:01"),
            new Carbon("2021-10-17 12:34:56"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "From intervals empty interval (empty, not empty)"
        );

        // 8. From intervals is empty (null, not null)
        $fromIntervals = [null];
        $removeInterval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "From intervals empty interval (null, not null)"
        );

        // 8. From intervals is empty (null, null)
        $fromIntervals = null;
        $removeInterval = null;

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "From intervals empty interval ([], null)"
        );

        // 8. From intervals is empty ([], null)
        $fromIntervals = [];
        $removeInterval = null;

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "From intervals empty interval ([], null)"
        );

        // 8. From intervals is empty ([null], null)
        $fromIntervals = [null];
        $removeInterval = null;

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "From intervals empty interval ([null], null)"
        );

        // 8. From intervals is empty (empty, same empty)
        $fromIntervals = [
            [
                new Carbon("2021-10-15 23:45:01"),
                new Carbon("2021-10-15 23:45:01"),
            ],
        ];
        $removeInterval = [
            new Carbon("2021-10-15 23:45:01"),
            new Carbon("2021-10-15 23:45:01"),
        ];

        $intervals = DateIntervalHelper::removeInterval(
            $fromIntervals,
            $removeInterval
        );

        $expected = [];
        $this->assertSameIntervals(
            $expected,
            $intervals,
            "From intervals empty interval (empty, same empty)"
        );
    }

    public function testCover()
    {
        $intervals = [
            [
                new Carbon("2021-10-10 12:34:56"),
                new Carbon("2021-10-15 18:43:01"),
            ],
            [
                new Carbon("2021-10-15 18:43:01"),
                new Carbon("2021-10-19 23:45:01"),
            ],
        ];

        $toCover = [
            new Carbon("2021-10-12 12:34:56"),
            new Carbon("2021-10-18 18:43:01"),
        ];
        $this->assertTrue(DateIntervalHelper::cover($intervals, $toCover));

        $toCover = [
            new Carbon("2021-10-17 12:34:56"),
            new Carbon("2021-10-20 18:43:01"),
        ];
        $this->assertFalse(DateIntervalHelper::cover($intervals, $toCover));
    }
}
