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
            "duration_in_minutes" => 20,
            "estimated_distance" => 0,
        ])->assertStatus(200);

        // Departure missing
        $this->json("GET", "/api/v1/loanables/search", [
            "duration_in_minutes" => 20,
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
            "duration_in_minutes" => 20,
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
                ->addDay(1)
                ->format("Y-m-d H:i:s"),
        ]);
        // Non-overlapping loan before
        $loanBefore = factory(Loan::class)->create([
            "loanable_id" => $carToFind->id,
            "departure_at" => Carbon::now()
                ->subDay(1)
                ->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 20,
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
            "duration_in_minutes" => 20,
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
        $carToIgnore = factory(Car::class)->create([
            "availability_mode" => "always",
            "owner_id" => $owner->id,
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 20,
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
                ->subMinute(5)
                ->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 20,
        ]);
        factory(Intention::class)->create([
            "status" => "completed",
            "loan_id" => $loan->id,
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 20,
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

        $carToIgnore = factory(Car::class)->create([
            "community_id" => $community->id,
            "availability_mode" => "never",
            "owner_id" => $owner->id,
        ]);

        $response = $this->json("GET", "/api/v1/loanables/search", [
            "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
            "duration_in_minutes" => 20,
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
            "duration_in_minutes" => 20,
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
        $loanable = factory(Bike::class)->create([
            "community_id" => $community->id,
        ]);

        $this->setTestLocale();

        // Complete valid request
        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/test",
            [
                "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
                "duration_in_minutes" => 20,
                "estimated_distance" => 0,
                "loanable_id" => $loanable->id,
                "community_id" => $community->id,
            ]
        )->assertStatus(200);

        // Departure missing
        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/test",
            [
                "duration_in_minutes" => 20,
                "estimated_distance" => 0,
                "loanable_id" => $loanable->id,
                "community_id" => $community->id,
            ]
        )
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "departure_at" => ["validation.required"],
                ],
            ]);

        // Community missing: OK
        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/test",
            [
                "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
                "duration_in_minutes" => 20,
                "estimated_distance" => 0,
                "loanable_id" => $loanable->id,
            ]
        )->assertStatus(200);

        // Loanable missing
        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/test",
            [
                "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
                "duration_in_minutes" => 20,
                "estimated_distance" => 0,
            ]
        )
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "loanable_id" => ["validation.required"],
                ],
            ]);

        // Duration 0
        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/test",
            [
                "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
                "duration_in_minutes" => 0,
                "estimated_distance" => 0,
                "loanable_id" => $loanable->id,
            ]
        )
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "duration_in_minutes" => ["validation.min.numeric"],
                ],
            ]);

        // Estimated distance negative
        $response = $this->json(
            "GET",
            "/api/v1/loanables/{$loanable->id}/test",
            [
                "departure_at" => Carbon::now()->format("Y-m-d H:i:s"),
                "duration_in_minutes" => 0,
                "estimated_distance" => -1,
                "loanable_id" => $loanable->id,
            ]
        )
            ->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "estimated_distance" => ["validation.min.numeric"],
                ],
            ]);
    }
}
