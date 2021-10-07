<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
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

    public function testLoanableTestEndpointValidation()
    {
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
