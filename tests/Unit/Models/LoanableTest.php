<?php

namespace Tests\Unit\Models;

use App\Models\Borrower;
use App\Models\Community;
use App\Models\Bike;
use App\Models\Car;
use App\Models\Loan;
use App\Models\Loanable;
use App\Models\Owner;
use App\Models\Trailer;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Unit\Calendar\AssertsIntervals;

class LoanableTest extends TestCase
{
    use AssertsIntervals;

    public $borough;
    public $community;
    public $otherCommunity;

    public $memberOfBorough;
    public $memberOfCommunity;
    public $otherMemberOfCommunity;
    public $memberOfOtherCommunity;

    public $communityLoanable; // TODO
    public $boroughLoanable; // TODO

    public function setUp(): void
    {
        parent::setUp();

        $this->borough = factory(Community::class)->create([
            "type" => "borough",
        ]);
        $this->community = factory(Community::class)->create([
            "parent_id" => $this->borough->id,
        ]);
        $this->otherCommunity = factory(Community::class)->create([
            "parent_id" => $this->borough->id,
        ]);

        $this->memberOfBorough = factory(User::class)->create([
            "name" => "memberOfBorough",
        ]);
        $this->borough->users()->attach($this->memberOfBorough, [
            "approved_at" => new \DateTime(),
        ]);

        $this->memberOfCommunity = factory(User::class)->create([
            "name" => "memberOfCommunity",
        ]);
        $this->community->users()->attach($this->memberOfCommunity, [
            "approved_at" => new \DateTime(),
        ]);

        $this->otherMemberOfCommunity = factory(User::class)->create([
            "name" => "otherMemberOfCommunity",
        ]);
        $this->community->users()->attach($this->otherMemberOfCommunity);

        $this->memberOfOtherCommunity = factory(User::class)->create([
            "name" => "memberOfOtherCommunity",
        ]);
        $this->otherCommunity->users()->attach($this->memberOfOtherCommunity, [
            "approved_at" => new \DateTime(),
        ]);

        foreach (
            [
                $this->memberOfBorough,
                $this->memberOfCommunity,
                $this->otherMemberOfCommunity,
                $this->memberOfOtherCommunity,
            ]
            as $member
        ) {
            $member->owner = new Owner();
            $member->owner->user()->associate($member);
            $member->owner->save();
        }

        foreach (
            [
                $this->memberOfCommunity,
                $this->otherMemberOfCommunity,
                $this->memberOfOtherCommunity,
            ]
            as $member
        ) {
            factory(Trailer::class)->create([
                "name" => "$member->name trailer",
                "owner_id" => $member->owner->id,
            ]);
        }
    }

    public function testAvailabilityParsePeriodStr()
    {
        // Without seconds
        $period = Loanable::availabilityParsePeriodStr("00:00-00:00");
        $expected = [[0, 0, 0], [0, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = Loanable::availabilityParsePeriodStr("12:34-23:45");
        $expected = [[12, 34, 0], [23, 45, 0]];
        $this->assertEquals($expected, $period);

        // Last minute exception
        $period = Loanable::availabilityParsePeriodStr("23:59-23:59");
        $expected = [[23, 59, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = Loanable::availabilityParsePeriodStr("23:59-24:00");
        $expected = [[23, 59, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        // With seconds
        $period = Loanable::availabilityParsePeriodStr("00:00:00-00:00:00");
        $expected = [[0, 0, 0], [0, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = Loanable::availabilityParsePeriodStr("12:34:56-23:45:01");
        $expected = [[12, 34, 56], [23, 45, 1]];
        $this->assertEquals($expected, $period);

        // Last minute exception
        $period = Loanable::availabilityParsePeriodStr("23:59:00-23:59:00");
        $expected = [[23, 59, 0], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = Loanable::availabilityParsePeriodStr("23:59:05-23:59:05");
        $expected = [[23, 59, 5], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = Loanable::availabilityParsePeriodStr("23:59:59-23:59:59");
        $expected = [[23, 59, 59], [24, 0, 0]];
        $this->assertEquals($expected, $period);

        $period = Loanable::availabilityParsePeriodStr("23:59:59-24:00:00");
        $expected = [[23, 59, 59], [24, 0, 0]];
        $this->assertEquals($expected, $period);
    }

    public function testAvailabilityGetDatesIntervals()
    {
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = Loanable::availabilityGetDatesIntervals($rule, $dateRange);
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

        $intervals = Loanable::availabilityGetDatesIntervals($rule, $dateRange);
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

        $intervals = Loanable::availabilityGetDatesIntervals($rule, $dateRange);
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

        $intervals = Loanable::availabilityGetDatesIntervals($rule, $dateRange);
        $expected = [];
        $this->assertSameIntervals($expected, $intervals);

        // Test without period.
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-09", "2021-11-12", "2021-11-13"],
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = Loanable::availabilityGetDatesIntervals($rule, $dateRange);
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

        $intervals = Loanable::availabilityGetDatesIntervals($rule, $dateRange);
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

        $intervals = Loanable::availabilityGetDatesIntervals($rule);
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

    public function testAvailabilityGetDateRangeIntervals()
    {
        $rule = [
            "type" => "dateRange",
            "scope" => ["2021-11-07", "2021-11-08", "2021-11-09"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = Loanable::availabilityGetDateRangeIntervals(
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

        $intervals = Loanable::availabilityGetDateRangeIntervals(
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

        $intervals = Loanable::availabilityGetDateRangeIntervals(
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

        $intervals = Loanable::availabilityGetDateRangeIntervals(
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

        $intervals = Loanable::availabilityGetDateRangeIntervals(
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

        $intervals = Loanable::availabilityGetDateRangeIntervals(
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

        $intervals = Loanable::availabilityGetDateRangeIntervals(
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

        $intervals = Loanable::availabilityGetDateRangeIntervals($rule);
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

    public function testAvailabilityGetWeekdaysIntervals()
    {
        $rule = [
            "type" => "weekdays",
            "scope" => ["SU", "WE", "SA"],
            "period" => "00:00-24:00",
        ];
        $dateRange = [new Carbon("2021-11-07"), new Carbon("2021-11-14")];

        $intervals = Loanable::availabilityGetWeekdaysIntervals(
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

        $intervals = Loanable::availabilityGetWeekdaysIntervals(
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

        $intervals = Loanable::availabilityGetWeekdaysIntervals(
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

        $intervals = Loanable::availabilityGetWeekdaysIntervals(
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

        $intervals = Loanable::availabilityGetWeekdaysIntervals(
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

    public function testGetPeriodIntervals()
    {
        // Make getPeriodIntervals accessible
        $class = new \ReflectionClass("App\Models\Loanable");
        $getPeriodIntervals = $class->getMethod("getPeriodIntervals");
        $getPeriodIntervals->setAccessible(true);

        $baseDate = new Carbon(
            "2021-11-01",
            new \DateTimeZone("America/Toronto")
        );

        // Basic case.
        $startTime = ["01", "00"];
        $endTime = ["23", "00"];

        $intervals = $getPeriodIntervals->invokeArgs(new Loanable(), [
            $baseDate,
            $startTime,
            $endTime,
        ]);

        // Expect two intervals.
        $this->assertEquals(2, count($intervals));

        $this->assertEquals(
            "2021-11-01T04:00:00.000000Z",
            $intervals[0][0]->toIsoString()
        );
        $this->assertEquals(
            "2021-11-01T05:00:00.000000Z",
            $intervals[0][1]->toIsoString()
        );

        $this->assertEquals(
            "2021-11-02T03:00:00.000000Z",
            $intervals[1][0]->toIsoString()
        );
        $this->assertEquals(
            "2021-11-02T03:59:59.000000Z",
            $intervals[1][1]->toIsoString()
        );

        // Start of day
        $startTime = ["00", "00"];
        $endTime = ["23", "00"];

        $intervals = $getPeriodIntervals->invokeArgs(new Loanable(), [
            $baseDate,
            $startTime,
            $endTime,
        ]);

        // Expect one interval.
        $this->assertEquals(1, count($intervals));

        $this->assertEquals(
            "2021-11-02T03:00:00.000000Z",
            $intervals[0][0]->toIsoString()
        );
        $this->assertEquals(
            "2021-11-02T03:59:59.000000Z",
            $intervals[0][1]->toIsoString()
        );

        // End of day
        $startTime = ["01", "00"];
        $endTime = ["23", "59"];

        $intervals = $getPeriodIntervals->invokeArgs(new Loanable(), [
            $baseDate,
            $startTime,
            $endTime,
        ]);

        // Expect one interval.
        $this->assertEquals(1, count($intervals));

        $this->assertEquals(
            "2021-11-01T04:00:00.000000Z",
            $intervals[0][0]->toIsoString()
        );
        $this->assertEquals(
            "2021-11-01T05:00:00.000000Z",
            $intervals[0][1]->toIsoString()
        );

        // Whole day
        $startTime = ["00", "00"];
        $endTime = ["23", "59"];

        $intervals = $getPeriodIntervals->invokeArgs(new Loanable(), [
            $baseDate,
            $startTime,
            $endTime,
        ]);

        // Expect no interval
        $this->assertEquals(0, count($intervals));
    }

    public function testLoanableNotAccessibleAccrossCommunitiesByDefault()
    {
        foreach (
            [
                $this->memberOfCommunity,
                $this->otherMemberOfCommunity,
                $this->memberOfOtherCommunity,
            ]
            as $member
        ) {
            $loanables = Loanable::accessibleBy($member)->pluck("name");
            $loanableIds = Loanable::accessibleBy($member)->pluck("id");
            $this->assertEquals(
                1,
                $loanables->count(),
                "too many loanables accessible to $member->name"
            );
            $this->assertEquals(
                $member->loanables()->first()->id,
                $loanableIds->first()
            );
        }
    }

    public function testLoanableBecomesAccessibleIfCommunityMembershipIsApproved()
    {
        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck(
            "id"
        );
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables->first()
        );

        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)
            ->pluck("id")
            ->sort();
        $this->assertEquals(2, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );
    }

    public function testCarBecomesAccessibleIfBorrowerIsApproved()
    {
        $car = factory(Car::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck(
            "id"
        );
        $this->assertEquals(2, $loanables->count());

        $loanables = Loanable::accessibleBy(
            $this->otherMemberOfCommunity
        )->pluck("id");
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );

        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $loanables = Loanable::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(2, $loanables->count());
        $firstId = $this->memberOfCommunity->loanables()->first()->id;
        $this->assertEquals(
            $firstId,
            $loanables[0],
            "$firstId not in " . implode(",", $loanables->toArray())
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );

        $borrower = new Borrower();
        $borrower->user()->associate($this->otherMemberOfCommunity);
        $borrower->approved_at = new \DateTime();
        $borrower->save();

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $loanables = Loanable::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(3, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );
        $this->assertEquals($car->id, $loanables[2]);
    }

    public function testLoanableAccessibleThroughInheritedClasses()
    {
        factory(Car::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
        ]);

        $bikes = Bike::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(0, $bikes->count());

        $trailers = Trailer::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(1, $trailers->count());

        $cars = Car::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(1, $cars->count());

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(0, $cars->count());

        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(0, $cars->count());

        $borrower = new Borrower();
        $borrower->user()->associate($this->otherMemberOfCommunity);
        $borrower->approved_at = new \DateTime();
        $borrower->save();

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(1, $cars->count());
    }

    public function testLoanableAccessibleDownFromBorough()
    {
        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck(
            "id"
        );
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables->first()
        );

        $boroughLoanable = factory(Trailer::class)->create([
            "owner_id" => $this->memberOfBorough->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(2, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->memberOfBorough->loanables()->first()->id,
            $loanables[1]
        );
    }

    public function testLoanableIsAccessibleUpFromBoroughIfEnabled()
    {
        $boroughLoanable = factory(Trailer::class)->create([
            "owner_id" => $this->memberOfBorough->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfBorough)->pluck(
            "id"
        );
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfBorough->loanables[0]->id,
            $loanables[0]
        );

        $loanable = Trailer::find($this->memberOfCommunity->loanables[0]->id);
        $loanable->share_with_parent_communities = true;
        $loanable->save();

        $loanables = Loanable::accessibleBy($this->memberOfBorough)
            ->where("id", "!=", $this->memberOfBorough->loanables[0]->id)
            ->pluck("id");
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals($loanable->id, $loanables[0]);
    }

    public function testLoanableMethodGetCommunityForLoanBy()
    {
        $bike = factory(Bike::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
            "community_id" => $this->community->id,
        ]);

        // User is not approved on the loanable community
        // (you normally wouldn't be able to see the loanable to begin with)
        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals(null, $community);

        // User is approved on the loanable community
        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals($this->community->id, $community->id);

        // User is approved on a child community of the loanable community
        $bike->community_id = $this->borough->id;
        $bike->save();
        $bike = $bike->fresh();
        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals($this->community->id, $community->id);

        // User is approved on community of the loanable's owner
        $bike->community_id = null;
        $bike->save();
        $bike = $bike->fresh();

        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals($this->community->id, $community->id);
    }

    public function testAvailabilityGetScheduleDailyIntervals()
    {
        $bike = factory(Bike::class)->create([
            "availability_mode" => "never",
            "availability_json" => <<<JSON
[   {   "available":true,
        "type":"dates",
        "scope":["2021-12-07","2021-12-09"],
        "period":"11:00-13:00"},
    {   "available":true,
        "type":"dateRange",
        "scope":["2021-12-14","2021-12-16"],
        "period":"17:00-21:00"},
    {   "available":true,
        "type":"dateRange",
        "scope":["2021-12-22","2021-12-24"]},
    {   "available":true,
        "type":"weekdays",
        "scope":["SA"],
        "period":"12:00-13:00"},
    {   "available":true,
        "type":"weekdays",
        "scope":["MO","SU"],
        "period":"00:00-23:59"}
]
JSON
        ,
        ]);

        $dateRange = [new Carbon("2021-12-01"), new Carbon("2022-01-01")];
        $intervals = $bike->availabilityGetScheduleDailyIntervals($dateRange);

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

    public function testIsScheduleAvailable()
    {
        $bike = factory(Bike::class)->create([
            "availability_mode" => "never",
            "availability_json" => <<<JSON
[   {   "available":true,
        "type":"dates",
        "scope":["2021-12-07","2021-12-09"],
        "period":"11:00-13:00"},
    {   "available":true,
        "type":"dateRange",
        "scope":["2021-12-14","2021-12-16"],
        "period":"17:00-21:00"},
    {   "available":true,
        "type":"dateRange",
        "scope":["2021-12-22","2021-12-24"]},
    {   "available":true,
        "type":"weekdays",
        "scope":["SA"],
        "period":"12:00-13:00"},
    {   "available":true,
        "type":"weekdays",
        "scope":["MO","SU"],
        "period":"00:00-23:59"}
]
JSON
        ,
        ]);

        // "dates"
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-09 11:00:00"), 60)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-09 10:59:00"), 2)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-09 12:59:00"), 2)
        );

        // "dateRange"
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-14 18:00:00"), 180)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-14 16:59:00"), 2)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-14 20:59:00"), 2)
        );

        // Multi-day "dateRange"
        $this->assertTrue(
            $bike->isScheduleAvailable(
                new Carbon("2021-12-22 00:00:00"),
                72 * 60
            )
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-21 23:59:00"), 2)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-24 23:59:00"), 2)
        );

        // "weekdays"
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 12:00:00"), 60)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 11:59:00"), 2)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 12:59:00"), 2)
        );

        // Multi-day "weekdays"
        $this->assertTrue(
            $bike->isScheduleAvailable(
                new Carbon("2021-12-19 00:00:00"),
                48 * 60
            )
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 23:59:00"), 2)
        );
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-20 23:59:00"), 2)
        );

        // Match no rule.
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-07 13:00:00"), 15)
        );

        $bike = factory(Bike::class)->create([
            "availability_mode" => "always",
            "availability_json" => <<<JSON
[   {   "available":false,
        "type":"dates",
        "scope":["2021-12-07","2021-12-09"],
        "period":"11:00-13:00"},
    {   "available":false,
        "type":"dateRange",
        "scope":["2021-12-14","2021-12-16"],
        "period":"17:00-21:00"},
    {   "available":false,
        "type":"dateRange",
        "scope":["2021-12-22","2021-12-24"]},
    {   "available":false,
        "type":"weekdays",
        "scope":["SA"],
        "period":"12:00-13:00"},
    {   "available":false,
        "type":"weekdays",
        "scope":["MO","SU"],
        "period":"00:00-23:59"}
]
JSON
        ,
        ]);

        // "dates"
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-09 11:00:00"), 120)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-09 10:30:00"), 30)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-09 13:00:00"), 30)
        );

        // "dateRange"
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-14 17:00:00"), 240)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-14 16:30:00"), 30)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-14 21:00:00"), 30)
        );

        // Multi-day "dateRange"
        $this->assertFalse(
            $bike->isScheduleAvailable(
                new Carbon("2021-12-22 00:00:00"),
                72 * 60
            )
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-21 23:30:00"), 30)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-25 00:00:00"), 30)
        );

        // "weekdays"
        $this->assertFalse(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 12:00:00"), 60)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 11:30:00"), 30)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 13:00:00"), 30)
        );

        // Multi-day "weekdays"
        $this->assertFalse(
            $bike->isScheduleAvailable(
                new Carbon("2021-12-19 00:00:00"),
                48 * 60
            )
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-18 23:30:00"), 30)
        );
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-21 00:00:00"), 30)
        );

        // No rule.
        $this->assertTrue(
            $bike->isScheduleAvailable(new Carbon("2021-12-07 13:00:00"), 15)
        );

        // Always, never, default.
        // Json null (not even a string)
    }

    public function testIsAvailableEventIfLoanExistsWithIntentionInProcessOrCanceled()
    {
        $bike = factory(Bike::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
            "community_id" => $this->community->id,
        ]);

        $user = factory(User::class)
            ->states("withBorrower")
            ->create();
        $loan = factory(Loan::class)
            ->states("withInProcessIntention")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-10 10:10:00",
                "duration_in_minutes" => 60,
            ]);

        $canceledLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessPrePayment")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-11 10:10:00",
                "duration_in_minutes" => 60,
                "canceled_at" => now(),
            ]);

        $canceledPrePaymentLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withCanceledPrePayment")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-11 10:10:00",
                "duration_in_minutes" => 60,
            ]);

        $confirmedLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessPrePayment")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-12 10:10:00",
                "duration_in_minutes" => 60,
            ]);
        $confirmedLoan->intention->status = "completed";
        $confirmedLoan->intention->save();
        $confirmedLoan = $confirmedLoan->fresh();

        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-10 10:20:00", 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-10 11:20:00", 60)
        );

        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-11 10:20:00", 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-11 11:20:00", 60)
        );

        $this->assertEquals(
            false,
            $bike->isAvailable("3000-10-12 10:20:00", 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-12 11:20:00", 60)
        );
    }

    public function testIsAvailableEarlyIfPaidBeforeDurationInMinutes()
    {
        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "duration_in_minutes" => 60,
            ]);

        $bike = $loan->loanable;

        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(61, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(59, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(31, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(29, "minutes"), 60)
        );

        // The loan was completed earlier
        $payment = $loan->payment()->first();
        $payment->executed_at = Carbon::now()->add(30, "minutes");
        $payment->save();

        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(61, "minutes"), 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(59, "minutes"), 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(31, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(29, "minutes"), 60)
        );
    }
}
