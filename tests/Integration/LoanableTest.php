<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Car;
use App\Models\Community;
use App\Models\Intention;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Pricing;
use App\Models\Trailer;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class LoanableTest extends TestCase
{
    private static $getLoanablesResponseStructure = [
        "current_page",
        "data",
        "first_page_url",
        "from",
        "last_page",
        "last_page_url",
        "next_page_url",
        "path",
        "per_page",
        "prev_page_url",
        "to",
        "total",
    ];

    public function testAvailability_AvailabilityModeNever_ResponseModeAvailable()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Car::class)->create([
            "availability_mode" => "never",
            "availability_json" => <<<JSON
[
  {
    "available": true,
    "type": "weekdays",
    "scope": ["MO","TU","TH","WE","FR"],
    "period": "17:00-22:00"
  },{
    "available": true,
    "type": "weekdays",
    "scope": ["SA","SU"],
    "period":"00:00-24:00"
  },{
    "available": true,
    "type": "dates",
    "scope": ["2022-10-10","2022-10-12","2022-10-14"],
    "period": "13:00-17:00"
  },{
    "available": true,
    "type": "dateRange",
    "scope": ["2022-10-10","2022-10-14"],
    "period": "10:00-12:00"
  }
]
JSON
            ,
            "owner_id" => $owner->id,
        ]);

        $borrowerUser = factory(User::class)->create();
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
                "withInProcessHandover",
            ])
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-12 13:15:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 8.5 * 60,
            ]);

        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/availability",
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "responseMode" => "available",
            ]
        );

        $response->assertStatus(200)->assertExactJson([
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-10 00:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-10 10:00:00",
                "end" => "2022-10-10 12:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-10 13:00:00",
                "end" => "2022-10-10 22:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-11 10:00:00",
                "end" => "2022-10-11 12:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-11 17:00:00",
                "end" => "2022-10-11 22:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-12 10:00:00",
                "end" => "2022-10-12 12:00:00",
                "data" => ["available" => true],
            ],
            // Loan from 13:15 to 21:45
            [
                "start" => "2022-10-12 13:00:00",
                "end" => "2022-10-12 13:15:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-12 21:45:00",
                "end" => "2022-10-12 22:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-13 10:00:00",
                "end" => "2022-10-13 12:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-13 17:00:00",
                "end" => "2022-10-13 22:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-14 10:00:00",
                "end" => "2022-10-14 12:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-14 13:00:00",
                "end" => "2022-10-14 22:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-15 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "data" => ["available" => true],
            ],
        ]);
    }

    public function testAvailability_AvailabilityModeAlways_ResponseModeAvailable()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Car::class)->create([
            "availability_mode" => "always",
            "availability_json" => <<<JSON
[
  {
    "available": false,
    "type": "weekdays",
    "scope": ["MO","TU","TH","WE","FR"],
    "period": "17:00-22:00"
  },{
    "available": false,
    "type": "weekdays",
    "scope": ["SA","SU"],
    "period":"00:00-24:00"
  },{
    "available": false,
    "type": "dates",
    "scope": ["2022-10-10","2022-10-12","2022-10-14"],
    "period": "13:00-17:00"
  },{
    "available": false,
    "type": "dateRange",
    "scope": ["2022-10-10","2022-10-14"],
    "period": "10:00-12:00"
  }
]
JSON
            ,
            "owner_id" => $owner->id,
        ]);

        $borrowerUser = factory(User::class)->create();
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        // Loan that overlaps availability rules.
        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
                "withInProcessHandover",
            ])
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-12 09:00:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 13.5 * 60,
            ]);

        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/availability",
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "responseMode" => "available",
            ]
        );

        $response->assertStatus(200)->assertExactJson([
            [
                "start" => "2022-10-10 00:00:00",
                "end" => "2022-10-10 10:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-10 12:00:00",
                "end" => "2022-10-10 13:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-10 22:00:00",
                "end" => "2022-10-11 00:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-11 00:00:00",
                "end" => "2022-10-11 10:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-11 12:00:00",
                "end" => "2022-10-11 17:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-11 22:00:00",
                "end" => "2022-10-12 00:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-12 00:00:00",
                "end" => "2022-10-12 09:00:00",
                "data" => ["available" => true],
            ],
            // Loan from 09:00 to 22:30
            [
                "start" => "2022-10-12 22:30:00",
                "end" => "2022-10-13 00:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-13 00:00:00",
                "end" => "2022-10-13 10:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-13 12:00:00",
                "end" => "2022-10-13 17:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-13 22:00:00",
                "end" => "2022-10-14 00:00:00",
                "data" => ["available" => true],
            ],

            [
                "start" => "2022-10-14 00:00:00",
                "end" => "2022-10-14 10:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-14 12:00:00",
                "end" => "2022-10-14 13:00:00",
                "data" => ["available" => true],
            ],
            [
                "start" => "2022-10-14 22:00:00",
                "end" => "2022-10-15 00:00:00",
                "data" => ["available" => true],
            ],
        ]);
    }

    public function testAvailability_AvailabilityModeNever_ResponseModeUnavailable()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Car::class)->create([
            "availability_mode" => "never",
            "availability_json" => <<<JSON
[
  {
    "available": true,
    "type": "weekdays",
    "scope": ["MO","TU","TH","WE","FR"],
    "period": "17:00-22:00"
  },{
    "available": true,
    "type": "weekdays",
    "scope": ["SA","SU"],
    "period":"00:00-24:00"
  },{
    "available": true,
    "type": "dates",
    "scope": ["2022-10-10","2022-10-12","2022-10-14"],
    "period": "13:00-17:00"
  },{
    "available": true,
    "type": "dateRange",
    "scope": ["2022-10-10","2022-10-14"],
    "period": "10:00-12:00"
  }
]
JSON
            ,
            "owner_id" => $owner->id,
        ]);

        $borrowerUser = factory(User::class)->create();
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
                "withInProcessHandover",
            ])
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-12 13:15:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 8.5 * 60,
            ]);

        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/availability",
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "responseMode" => "unavailable",
            ]
        );

        $response->assertStatus(200)->assertExactJson([
            [
                "start" => "2022-10-10 00:00:00",
                "end" => "2022-10-10 10:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-10 12:00:00",
                "end" => "2022-10-10 13:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-10 22:00:00",
                "end" => "2022-10-11 00:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-11 00:00:00",
                "end" => "2022-10-11 10:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-11 12:00:00",
                "end" => "2022-10-11 17:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-11 22:00:00",
                "end" => "2022-10-12 00:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-12 00:00:00",
                "end" => "2022-10-12 10:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-12 12:00:00",
                "end" => "2022-10-12 13:00:00",
                "data" => ["available" => false],
            ],
            // Loan
            [
                "start" => "2022-10-12 13:15:00",
                "end" => "2022-10-12 21:45:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-12 22:00:00",
                "end" => "2022-10-13 00:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-13 00:00:00",
                "end" => "2022-10-13 10:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-13 12:00:00",
                "end" => "2022-10-13 17:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-13 22:00:00",
                "end" => "2022-10-14 00:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-14 00:00:00",
                "end" => "2022-10-14 10:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-14 12:00:00",
                "end" => "2022-10-14 13:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-14 22:00:00",
                "end" => "2022-10-15 00:00:00",
                "data" => ["available" => false],
            ],
        ]);
    }

    public function testAvailability_AvailabilityModeAlways_ResponseModeUnavailable()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Car::class)->create([
            "availability_mode" => "always",
            "availability_json" => <<<JSON
[
  {
    "available": false,
    "type": "weekdays",
    "scope": ["MO","TU","TH","WE","FR"],
    "period": "17:00-22:00"
  },{
    "available": false,
    "type": "weekdays",
    "scope": ["SA","SU"],
    "period":"00:00-24:00"
  },{
    "available": false,
    "type": "dates",
    "scope": ["2022-10-10","2022-10-12","2022-10-14"],
    "period": "13:00-17:00"
  },{
    "available": false,
    "type": "dateRange",
    "scope": ["2022-10-10","2022-10-14"],
    "period": "10:00-12:00"
  }
]
JSON
            ,
            "owner_id" => $owner->id,
        ]);

        $borrowerUser = factory(User::class)->create();
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
                "withInProcessHandover",
            ])
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-11 12:15:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 4.5 * 60,
            ]);

        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/availability",
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "responseMode" => "unavailable",
            ]
        );

        $response->assertStatus(200)->assertExactJson([
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-10 00:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-10 10:00:00",
                "end" => "2022-10-10 12:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-10 13:00:00",
                "end" => "2022-10-10 22:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-11 10:00:00",
                "end" => "2022-10-11 12:00:00",
                "data" => ["available" => false],
            ],
            // Loan
            [
                "start" => "2022-10-11 12:15:00",
                "end" => "2022-10-11 16:45:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-11 17:00:00",
                "end" => "2022-10-11 22:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-12 10:00:00",
                "end" => "2022-10-12 12:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-12 13:00:00",
                "end" => "2022-10-12 22:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-13 10:00:00",
                "end" => "2022-10-13 12:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-13 17:00:00",
                "end" => "2022-10-13 22:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-14 10:00:00",
                "end" => "2022-10-14 12:00:00",
                "data" => ["available" => false],
            ],
            [
                "start" => "2022-10-14 13:00:00",
                "end" => "2022-10-14 22:00:00",
                "data" => ["available" => false],
            ],

            [
                "start" => "2022-10-15 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "data" => ["available" => false],
            ],
        ]);
    }

    public function testEvents_AvailabilityModeNever()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Car::class)->create([
            "availability_mode" => "never",
            "availability_json" => <<<JSON
[
  {
    "available": true,
    "type": "weekdays",
    "scope": ["MO","TU","TH","WE","FR"],
    "period": "17:00-22:00"
  },{
    "available": true,
    "type": "weekdays",
    "scope": ["SA","SU"],
    "period":"00:00-24:00"
  },{
    "available": true,
    "type": "dates",
    "scope": ["2022-10-10","2022-10-12","2022-10-14"],
    "period": "13:00-17:00"
  },{
    "available": true,
    "type": "dateRange",
    "scope": ["2022-10-10","2022-10-14"],
    "period": "10:00-12:00"
  }
]
JSON
            ,
            "owner_id" => $owner->id,
        ]);

        $borrowerUser = factory(User::class)->create();
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);
        $loanLessThanOneDay = factory(Loan::class)
            ->states("withInProcessHandover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-11 11:15:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 10 * 60,
            ]);

        $loanMoreThanOneDay = factory(Loan::class)
            ->states("withInProcessHandover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-12 22:45:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 3 * 24 * 60,
            ]);

        $loanMoreThanOneMonth = factory(Loan::class)
            ->states("withInProcessHandover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-09-30 00:00:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 44 * 24 * 60,
            ]);

        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/events",
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-16 00:00:00",
                // Set order to get the results in a predictable order.
                "order" => "type,start,end",
            ]
        );

        $response->assertStatus(200)->assertJson([
            [
                // "type": "weekdays", "scope": ["SA","SU"], "period":"00:00-24:00"
                "type" => "availability_rule",
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-10 00:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-10 10:00:00",
                "end" => "2022-10-10 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dates", "scope": ["2022-10-10","2022-10-12","2022-10-14"], "period": "13:00-17:00"
                "type" => "availability_rule",
                "start" => "2022-10-10 13:00:00",
                "end" => "2022-10-10 17:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-10 17:00:00",
                "end" => "2022-10-10 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-11 10:00:00",
                "end" => "2022-10-11 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-11 17:00:00",
                "end" => "2022-10-11 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-12 10:00:00",
                "end" => "2022-10-12 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dates", "scope": ["2022-10-10","2022-10-12","2022-10-14"], "period": "13:00-17:00"
                "type" => "availability_rule",
                "start" => "2022-10-12 13:00:00",
                "end" => "2022-10-12 17:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-12 17:00:00",
                "end" => "2022-10-12 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-13 10:00:00",
                "end" => "2022-10-13 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-13 17:00:00",
                "end" => "2022-10-13 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-14 10:00:00",
                "end" => "2022-10-14 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "dates", "scope": ["2022-10-10","2022-10-12","2022-10-14"], "period": "13:00-17:00"
                "type" => "availability_rule",
                "start" => "2022-10-14 13:00:00",
                "end" => "2022-10-14 17:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-14 17:00:00",
                "end" => "2022-10-14 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            [
                // "type": "weekdays", "scope": ["SA","SU"], "period":"00:00-24:00"
                "type" => "availability_rule",
                "start" => "2022-10-15 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => true],
            ],
            // Loans
            [
                "type" => "loan",
                "start" => "2022-09-30 00:00:00",
                "end" => "2022-11-13 00:00:00",
                "uri" => "/loans/{$loanMoreThanOneMonth->id}",
                "data" => ["status" => "in_process"],
            ],
            [
                "type" => "loan",
                "start" => "2022-10-11 11:15:00",
                "end" => "2022-10-11 21:15:00",
                "uri" => "/loans/{$loanLessThanOneDay->id}",
                "data" => ["status" => "in_process"],
            ],
            [
                "type" => "loan",
                "start" => "2022-10-12 22:45:00",
                "end" => "2022-10-15 22:45:00",
                "uri" => "/loans/{$loanMoreThanOneDay->id}",
                "data" => ["status" => "in_process"],
            ],
        ]);
    }

    public function testEvents_AvailabilityModeAlways()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Car::class)->create([
            "availability_mode" => "always",
            "availability_json" => <<<JSON
[
  {
    "available": false,
    "type": "weekdays",
    "scope": ["MO","TU","TH","WE","FR"],
    "period": "17:00-22:00"
  },{
    "available": false,
    "type": "weekdays",
    "scope": ["SA","SU"],
    "period":"00:00-24:00"
  },{
    "available": false,
    "type": "dates",
    "scope": ["2022-10-10","2022-10-12","2022-10-14"],
    "period": "13:00-17:00"
  },{
    "available": false,
    "type": "dateRange",
    "scope": ["2022-10-10","2022-10-14"],
    "period": "10:00-12:00"
  }
]
JSON
            ,
            "owner_id" => $owner->id,
        ]);

        $borrowerUser = factory(User::class)->create();
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $loanLessThanOneDay = factory(Loan::class)
            ->states("withInProcessHandover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-11 11:15:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 10 * 60,
            ]);

        $loanMoreThanOneDay = factory(Loan::class)
            ->states("withInProcessHandover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-10-12 22:45:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 3 * 24 * 60,
            ]);

        $loanMoreThanOneMonth = factory(Loan::class)
            ->states("withInProcessHandover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => (new Carbon("2022-09-30 00:00:00"))->format(
                    "Y-m-d H:i:s"
                ),
                "duration_in_minutes" => 44 * 24 * 60,
            ]);

        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/events",
            [
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-16 00:00:00",
                // Set order to get the results in a predictable order.
                "order" => "type,start,end",
            ]
        );

        $response->assertStatus(200)->assertJson([
            [
                // "type": "weekdays", "scope": ["SA","SU"], "period":"00:00-24:00"
                "type" => "availability_rule",
                "start" => "2022-10-09 00:00:00",
                "end" => "2022-10-10 00:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-10 10:00:00",
                "end" => "2022-10-10 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dates", "scope": ["2022-10-10","2022-10-12","2022-10-14"], "period": "13:00-17:00"
                "type" => "availability_rule",
                "start" => "2022-10-10 13:00:00",
                "end" => "2022-10-10 17:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-10 17:00:00",
                "end" => "2022-10-10 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-11 10:00:00",
                "end" => "2022-10-11 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-11 17:00:00",
                "end" => "2022-10-11 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-12 10:00:00",
                "end" => "2022-10-12 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dates", "scope": ["2022-10-10","2022-10-12","2022-10-14"], "period": "13:00-17:00"
                "type" => "availability_rule",
                "start" => "2022-10-12 13:00:00",
                "end" => "2022-10-12 17:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-12 17:00:00",
                "end" => "2022-10-12 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-13 10:00:00",
                "end" => "2022-10-13 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-13 17:00:00",
                "end" => "2022-10-13 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dateRange", "scope": ["2022-10-10","2022-10-14"], "period": "10:00-12:00"
                "type" => "availability_rule",
                "start" => "2022-10-14 10:00:00",
                "end" => "2022-10-14 12:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "dates", "scope": ["2022-10-10","2022-10-12","2022-10-14"], "period": "13:00-17:00"
                "type" => "availability_rule",
                "start" => "2022-10-14 13:00:00",
                "end" => "2022-10-14 17:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "weekdays", "scope": ["MO","TU","TH","WE","FR"], "period": "17:00-22:00"
                "type" => "availability_rule",
                "start" => "2022-10-14 17:00:00",
                "end" => "2022-10-14 22:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            [
                // "type": "weekdays", "scope": ["SA","SU"], "period":"00:00-24:00"
                "type" => "availability_rule",
                "start" => "2022-10-15 00:00:00",
                "end" => "2022-10-16 00:00:00",
                "uri" => "/loanables/{$loanable->id}",
                "data" => ["available" => false],
            ],
            // Loans
            [
                "type" => "loan",
                "start" => "2022-09-30 00:00:00",
                "end" => "2022-11-13 00:00:00",
                "uri" => "/loans/{$loanMoreThanOneMonth->id}",
                "data" => ["status" => "in_process"],
            ],
            [
                "type" => "loan",
                "start" => "2022-10-11 11:15:00",
                "end" => "2022-10-11 21:15:00",
                "uri" => "/loans/{$loanLessThanOneDay->id}",
                "data" => ["status" => "in_process"],
            ],
            [
                "type" => "loan",
                "start" => "2022-10-12 22:45:00",
                "end" => "2022-10-15 22:45:00",
                "uri" => "/loans/{$loanMoreThanOneDay->id}",
                "data" => ["status" => "in_process"],
            ],
        ]);
    }

    public function testListLoanablesValidation()
    {
        // Avoid triggering emails
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Car::class)->create([
            "owner_id" => $owner->id,
        ]);

        // Complete valid request
        $this->json("GET", "/api/v1/loanables/list")->assertStatus(200);
        $this->json("GET", "/api/v1/loanables/list?types=car")->assertStatus(
            200
        );
        $this->json(
            "GET",
            "/api/v1/loanables/list?types=car,bike,trailer"
        )->assertStatus(200);
        $this->json("GET", "/api/v1/loanables/list?types=couch")
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "types" => [
                        "Les types demandés (couch) sont invalides. Options possibles: bike,car,trailer.",
                    ],
                ],
            ]);
    }

    public function testListLoanables_doesntReturnUnaccessibleLoanables()
    {
        // Avoid triggering emails
        $this->withoutEvents();

        $community = factory(Community::class)->create();
        $otherCommunity = factory(Community::class)->create();

        $borrowerUser = factory(User::class)->create();
        $borrowerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $otherCommunity->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Car::class)->create([
            "owner_id" => $owner->id,
        ]);
        factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);
        factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);

        $this->actAs($borrowerUser);
        $this->json("GET", "/api/v1/loanables/list?types=car,trailer,bike")
            ->assertStatus(200)
            ->assertExactJson([
                "bikes" => [],
                "cars" => [],
                "trailers" => [],
            ]);
    }

    public function testListLoanables_returnsDetailedCars()
    {
        // Avoid triggering emails
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Car::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "always",
        ]);

        $this->json("GET", "/api/v1/loanables/list?types=car")
            ->assertStatus(200)
            ->assertJsonStructure([
                "cars" => [
                    "*" => [
                        "id",
                        "type",
                        "name",
                        "is_self_service",
                        "comments",
                        "owner" => [
                            "user" => [
                                "id",
                                "name",
                                "last_name",
                                "full_name",
                                "avatar",
                            ],
                        ],
                        "image" => [],
                        "brand",
                        "engine",
                        "transmission_mode",
                        "year_of_circulation",
                        "papers_location",
                        "model",
                    ],
                ],
            ])
            ->assertJsonCount(1, "cars");
    }

    public function testListLoanables_returnsDetailedBikes()
    {
        // Avoid triggering emails
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "always",
        ]);

        $this->json("GET", "/api/v1/loanables/list?types=bike")
            ->assertStatus(200)
            ->assertJsonStructure([
                "bikes" => [
                    "*" => [
                        "id",
                        "type",
                        "name",
                        "is_self_service",
                        "comments",
                        "owner" => [
                            "user" => [
                                "id",
                                "name",
                                "last_name",
                                "full_name",
                                "avatar",
                            ],
                        ],
                        "image" => [],
                        "bike_type",
                        "model",
                        "size",
                    ],
                ],
            ])
            ->assertJsonCount(1, "bikes");
    }

    public function testListLoanables_returnsDetailedTrailers()
    {
        // Avoid triggering emails
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Trailer::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "always",
        ]);

        $this->json("GET", "/api/v1/loanables/list?types=trailer")
            ->assertStatus(200)
            ->assertJsonStructure([
                "trailers" => [
                    "*" => [
                        "id",
                        "type",
                        "name",
                        "is_self_service",
                        "comments",
                        "owner" => [
                            "user" => [
                                "id",
                                "name",
                                "last_name",
                                "full_name",
                                "avatar",
                            ],
                        ],
                        "image" => [],
                        "maximum_charge",
                        "dimensions",
                    ],
                ],
            ])
            ->assertJsonCount(1, "trailers");
    }

    public function testListLoanables_hidesUnavailableLoanables()
    {
        // Avoid triggering emails
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Trailer::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "never",
        ]);
        factory(Car::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "never",
        ]);
        factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "never",
        ]);

        $this->json("GET", "/api/v1/loanables/list")
            ->assertStatus(200)
            ->assertJsonCount(0, "trailers")
            ->assertJsonCount(0, "cars")
            ->assertJsonCount(0, "bikes");
    }

    public function testListLoanables_showsPartiallyAvailableLoanables()
    {
        // Avoid triggering emails
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Trailer::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "never",
            "availability_json" =>
                '[{"available":true,"type":"weekdays","scope":["SU"],"period":"00:00-24:00"}]',
        ]);
        factory(Car::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "never",
            "availability_json" =>
                '[{"available":true,"type":"weekdays","scope":["SU"],"period":"00:00-24:00"}]',
        ]);
        factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "availability_mode" => "never",
            "availability_json" =>
                '[{"available":true,"type":"weekdays","scope":["SU"],"period":"00:00-24:00"}]',
        ]);

        $this->json("GET", "/api/v1/loanables/list")
            ->assertStatus(200)
            ->assertJsonCount(1, "trailers")
            ->assertJsonCount(1, "cars")
            ->assertJsonCount(1, "bikes");
    }

    public function testSearchLoanablesValidation()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Car::class)->create([
            "community_id" => $community->id,
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);

        $this->setTestLocale();

        // Complete valid request
        $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ])->assertStatus(200);

        // Departure missing
        $this->json("GET", "/api/v1/loanables/search", [
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ])
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "departure_at" => ["validation.required"],
                ],
            ]);

        // Distance missing
        $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
        ])
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "estimated_distance" => ["validation.required"],
                ],
            ]);

        // Duration 0
        $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 0,
            "estimated_distance" => 0,
        ])
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "duration_in_minutes" => ["validation.min.numeric"],
                ],
            ]);
    }

    public function testSearchLoanables_findsLoanable()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        $carToFind = factory(Car::class)->create([
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);

        $borrowerUser = factory(User::class)->create();
        factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        // Non-overlapping loan after
        $loanAfter = factory(Loan::class)->create([
            "loanable_id" => $carToFind->id,
            "departure_at" => Carbon::now()
                ->addDay()
                ->format("Y-m-d H:i:s"),
        ]);
        // Non-overlapping loan before
        $loanBefore = factory(Loan::class)->create([
            "loanable_id" => $carToFind->id,
            "departure_at" => Carbon::now()
                ->subDay()
                ->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
        ]);
        factory(Intention::class)->create([
            "status" => "completed",
            "loan_id" => $loanAfter->id,
        ]);
        factory(Intention::class)->create([
            "status" => "completed",
            "loan_id" => $loanBefore->id,
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ]);

        $response->assertJson([
            [
                "loanableId" => $carToFind->id,
                "estimatedCost" => [
                    "price" => 0,
                    "insurance" => 0,
                    "pricing" => "Gratuit",
                ],
            ],
        ]);
    }

    public function testSearchLoanables_ignoresLoanableFromOtherCommunity()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $otherCommunity = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $borrowerUser = factory(User::class)->create();
        factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $otherCommunity->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        factory(Car::class)->create([
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ]);

        $response->assertStatus(200)->assertJson([]);
    }

    public function testSearchLoanables_ignoresLoanableWithOverlappingLoans()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();

        $borrowerUser = factory(User::class)->create();
        factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);
        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        $carToIgnore = factory(Car::class)->create([
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);
        $loan = factory(Loan::class)->create([
            "loanable_id" => $carToIgnore->id,
            "departure_at" => Carbon::now()
                ->subMinutes(5)
                ->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
        ]);
        factory(Intention::class)->create([
            "status" => "completed",
            "loan_id" => $loan->id,
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ]);

        $response->assertStatus(200)->assertJson([]);
    }

    public function testSearchLoanables_ignoresLoanableWhenUnavailable()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);

        $borrowerUser = factory(User::class)->create();
        factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);
        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);
        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        factory(Car::class)->create([
            "community_id" => $community->id,
            "availability_mode" => "never",
            "owner_id" => $owner->id,
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ]);

        $response->assertStatus(200)->assertJson([]);
    }

    public function testSearchLoanables_estimatesWithPricing()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();
        $community = factory(Community::class)->create();
        $this->user->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);

        $borrowerUser = factory(User::class)->create();
        factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($borrowerUser);
        $borrowerUser->communities()->attach($community->id, [
            "approved_at" => Carbon::now(),
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);
        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $carToFind = factory(Car::class)->create([
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);

        $bikeToFind = factory(Bike::class)->create([
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);

        $trailerToFind = factory(Trailer::class)->create([
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);

        factory(Pricing::class)->create([
            "community_id" => $community->id,
            "rule" => "[10,3]",
            "object_type" => "App\Models\Car",
            "name" => "car pricing name",
        ]);
        factory(Pricing::class)->create([
            "community_id" => $community->id,
            "rule" => "7",
            "object_type" => "App\Models\Bike",
            "name" => "bike pricing name",
        ]);
        factory(Pricing::class)->create([
            "community_id" => $community->id,
            "rule" => "0",
            "object_type" => null,
            "name" => "default pricing",
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ]);

        $response->assertJson([
            [
                "loanableId" => $carToFind->id,
                "estimatedCost" => [
                    "price" => 10,
                    "insurance" => 3,
                    "pricing" => "car pricing name",
                ],
            ],
            [
                "loanableId" => $bikeToFind->id,
                "estimatedCost" => [
                    "price" => 7,
                    "insurance" => 0,
                    "pricing" => "bike pricing name",
                ],
            ],
            [
                "loanableId" => $trailerToFind->id,
                "estimatedCost" => [
                    "price" => 0,
                    "insurance" => 0,
                    "pricing" => "default pricing",
                ],
            ],
        ]);
    }

    public function testOrderLoanablesById()
    {
        $data = [
            "order" => "id",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "id,name,type,owner.id,owner.user.full_name,owner.user.id,deleted_at",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testOrderLoanablesByName()
    {
        $data = [
            "order" => "name",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "id,name,type,owner.id,owner.user.full_name,owner.user.id,deleted_at",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testOrderLoanablesByType()
    {
        $data = [
            "order" => "type",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "id,name,type,owner.id,owner.user.full_name,owner.user.id,deleted_at",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testOrderLoanablesByOwnerFullName()
    {
        $data = [
            "order" => "owner.user.full_name",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "id,name,type,owner.id,owner.user.full_name,owner.user.id,deleted_at",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testFilterLoanablesById()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
            "id" => "4",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testFilterLoanablesByName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
            "name" => "Vélo",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testFilterLoanablesByType()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,type,parent.id,parent.name",
            "type" => "car",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testFilterLoanablesByDeletedAt()
    {
        // Lower bound only
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "deleted_at" => "2020-11-10T01:23:45Z@",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);

        // Lower and upper bounds
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "deleted_at" => "2020-11-10T01:23:45Z@2020-11-12T01:23:45Z",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);

        // Upper bound only
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "deleted_at" => "@2020-11-12T01:23:45Z",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);

        // Degenerate case when bounds are removed
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "deleted_at" => "@",
        ];
        $response = $this->json("GET", "/api/v1/loanables/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoanablesResponseStructure);
    }

    public function testRetrieveNextLoan()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();

        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);

        $community = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);

        $owner = factory(Owner::class)->create(["user_id" => $user->id]);
        $loanable = factory(Bike::class)->create(["owner_id" => $owner->id]);

        $departure = new Carbon();

        $data = factory(Loan::class)
            ->make([
                "duration_in_minutes" => 30,
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "community_id" => $community->id,
            ])
            ->toArray();

        // Create a loan that departs in 1 hour and lasts 30 minutes.
        $response = $this->json(
            "POST",
            "/api/v1/loans",
            array_merge($data, [
                "departure_at" => $departure
                    ->add(1, "hour")
                    ->toDateTimeString(),
            ])
        );
        $response->assertStatus(201);
        $nextLoanId = $response->json()["id"];

        // Create a loan that departs in 2 hours and lasts 30 minutes.
        $response = $this->json(
            "POST",
            "/api/v1/loans",
            array_merge($data, [
                "departure_at" => $departure
                    ->add(2, "hour")
                    ->toDateTimeString(),
            ])
        );
        $response->assertStatus(201);
        $nextNextLoanId = $response->json()["id"];

        // Create a loan that departed 4 hours ago and lasts 30 minutes.
        $response = $this->json(
            "POST",
            "/api/v1/loans",
            array_merge($data, [
                "departure_at" => $departure
                    ->subtract(4, "hour")
                    ->toDateTimeString(),
            ])
        );
        $response->assertStatus(201);
        $currentLoanId = $response->json()["id"];

        $now = new Carbon();

        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/loans",
            [
                "order" => "departure_at",
                "departure_at" => $now->toISOString() . "@",
                "!id" => $currentLoanId,
                "per_page" => 1,
            ]
        );
        $response->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => $nextLoanId,
                ],
            ],
        ]);

        // Shortcut request
        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/loans/{$nextLoanId}/next"
        );
        $response->assertStatus(200)->assertJson([
            "id" => $nextNextLoanId,
        ]);
    }

    public function testRetrieveLoanableForOwner_showsInstructions()
    {
        $ownerUser = factory(User::class)->create();
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);

        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "instructions" => "test",
        ]);

        $this->actAs($ownerUser);
        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}");

        $response->assertJsonFragment([
            "instructions" => "test",
        ]);
    }

    public function testRetrieveLoanableForAdmin_showsInstructions()
    {
        $ownerUser = factory(User::class)->create();
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);
        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "instructions" => "test",
        ]);

        $admin = factory(User::class)->create(["role" => "admin"]);

        $this->actAs($admin);
        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}");

        $response->assertJsonFragment([
            "instructions" => "test",
        ]);
    }

    public function testRetrieveLoanable_hidesInstructions()
    {
        $this->withoutEvents();

        $community = factory(Community::class)->create();

        $ownerUser = factory(User::class)->create();
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);
        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "instructions" => "test",
        ]);

        $otherUser = factory(User::class)->create();

        // Other user has access to loanable but not to instructions
        $otherUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);

        $this->actAs($otherUser);
        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}");

        $response->assertJsonMissing([
            "instructions" => "test",
        ]);
    }

    public function testLoanableTestEndpointValidation()
    {
        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $this->user->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);
        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);
        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);

        $this->setTestLocale();

        // Complete valid request
        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}/test", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
            "loanable_id" => $loanable->id,
            "community_id" => $community->id,
        ])->assertStatus(200);

        // Departure missing
        $this->json("GET", "/api/v1/loanables/{$loanable->id}/test", [
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
            "loanable_id" => $loanable->id,
            "community_id" => $community->id,
        ])
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "departure_at" => ["validation.required"],
                ],
            ]);

        // Community missing: OK
        $this->json("GET", "/api/v1/loanables/{$loanable->id}/test", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
            "loanable_id" => $loanable->id,
        ])->assertStatus(200);

        // Loanable missing
        $this->json("GET", "/api/v1/loanables/{$loanable->id}/test", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 30,
            "estimated_distance" => 0,
        ])
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "loanable_id" => ["validation.required"],
                ],
            ]);

        // Duration 0
        $this->json("GET", "/api/v1/loanables/{$loanable->id}/test", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 0,
            "estimated_distance" => 0,
            "loanable_id" => $loanable->id,
        ])
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "duration_in_minutes" => ["validation.min.numeric"],
                ],
            ]);

        // Estimated distance negative
        $this->json("GET", "/api/v1/loanables/{$loanable->id}/test", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 0,
            "estimated_distance" => -1,
            "loanable_id" => $loanable->id,
        ])
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "estimated_distance" => ["validation.min.numeric"],
                ],
            ]);
    }

    public function testAddCoowner()
    {
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $coOwnerUser = factory(User::class)->create();
        $coOwnerUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);

        $this->actAs($ownerUser);

        $this->json("PUT", "/api/v1/loanables/{$loanable->id}/coowners", [
            "user_id" => $coOwnerUser->id,
        ])->assertStatus(200);
        $loanable->refresh();

        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}", [
            "fields" => "coowners.*",
        ]);
        $response->assertJson([
            "coowners" => [
                [
                    "user_id" => $coOwnerUser->id,
                    "loanable_id" => $loanable->id,
                ],
            ],
        ]);
    }

    public function testAddCoowner_failsWithNoSharedCommunities()
    {
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $coOwnerUser = factory(User::class)->create();

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);

        $this->actAs($ownerUser);

        $this->json("PUT", "/api/v1/loanables/{$loanable->id}/coowners", [
            "user_id" => $coOwnerUser->id,
        ])->assertStatus(422);
        $loanable->refresh();

        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}", [
            "fields" => "coowners.*",
        ]);
        $response->assertJson([
            "coowners" => [],
        ]);
    }

    public function testAddCoowner_failsForSelf()
    {
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $coOwnerUser = factory(User::class)->create();
        $coOwnerUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);

        $this->actAs($coOwnerUser);

        $this->json("PUT", "/api/v1/loanables/{$loanable->id}/coowners", [
            "user_id" => $coOwnerUser->id,
        ])->assertStatus(403);
        $loanable->refresh();
    }

    public function testRemoveCoowner()
    {
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $coOwnerUser = factory(User::class)->create();
        $coOwnerUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);
        $loanable->addCoowner($coOwnerUser->id);

        $this->actAs($ownerUser);
        $this->json("DELETE", "/api/v1/loanables/{$loanable->id}/coowners", [
            "user_id" => $coOwnerUser->id,
        ])->assertStatus(200);
        $loanable->refresh();

        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}", [
            "fields" => "coowners.*",
        ]);
        $response->assertJsonCount(0, "coowners");
    }

    public function testRemoveCoowner_succeedsForSelf()
    {
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $coOwnerUser = factory(User::class)->create();
        $coOwnerUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);
        $loanable->addCoowner($coOwnerUser->id);

        $this->actAs($coOwnerUser);
        $this->json("DELETE", "/api/v1/loanables/{$loanable->id}/coowners", [
            "user_id" => $coOwnerUser->id,
        ])->assertStatus(200);
        $loanable->refresh();

        $response = $this->json("GET", "/api/v1/loanables/{$loanable->id}", [
            "fields" => "coowners.*",
        ]);
        $response->assertJsonCount(0, "coowners");
    }

    public function testRemoveCoowner_failsForDifferentCoowner()
    {
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $coOwnerUser = factory(User::class)->create();
        $coOwnerUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $otherCoOwnerUser = factory(User::class)->create();
        $otherCoOwnerUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);
        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);
        $loanable->addCoowner($coOwnerUser->id);
        $loanable->addCoowner($otherCoOwnerUser->id);

        $this->actAs($coOwnerUser);
        $this->json("DELETE", "/api/v1/loanables/{$loanable->id}/coowners", [
            "user_id" => $otherCoOwnerUser->id,
        ])->assertStatus(403);
    }

    public function testRemoveCoowner_failsForNonCoowner()
    {
        $this->withoutEvents();

        $community = factory(Community::class)
            ->states("withDefaultFreePricing")
            ->create();
        $randomUser = factory(User::class)->create();
        $randomUser->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser->communities()->sync([
            $community->id => [
                "approved_at" => Carbon::now(),
            ],
        ]);

        $owner = factory(Owner::class)->create([
            "user_id" => $ownerUser->id,
        ]);

        $loanable = factory(Trailer::class)->create([
            "owner_id" => $owner->id,
        ]);

        $this->actAs($ownerUser);
        $this->json("DELETE", "/api/v1/loanables/{$loanable->id}/coowners", [
            "user_id" => $randomUser->id,
        ])->assertStatus(422);
    }
}
