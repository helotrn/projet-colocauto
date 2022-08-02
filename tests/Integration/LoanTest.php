<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Extension;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class LoanTest extends TestCase
{
    private static $getLoanResponseStructure = [
        "id",
        "departure_at",
        "duration_in_minutes",
        "borrower_id",
        "estimated_distance",
    ];

    public function testOrderLoansById()
    {
        $data = [
            "order" => "id",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "*,borrower.user.full_name,loanable.owner.user.full_name,community.name",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testOrderLoansByDepartureAt()
    {
        $data = [
            "order" => "departure_at",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "*,borrower.user.full_name,loanable.owner.user.full_name,community.name",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testOrderLoansByBorrowerFullName()
    {
        $data = [
            "order" => "borrower.user.full_name",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "*,borrower.user.full_name,loanable.owner.user.full_name,community.name",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testOrderLoansByOwnerFullName()
    {
        $data = [
            "order" => "loanable.owner.user.full_name",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "*,borrower.user.full_name,loanable.owner.user.full_name,community.name",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testOrderLoansByCommunityName()
    {
        $data = [
            "order" => "community.name",
            "page" => 1,
            "per_page" => 10,
            "fields" =>
                "*,borrower.user.full_name,loanable.owner.user.full_name,community.name",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoanByDepartureAt()
    {
        // Lower bound only
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "departure_at" => "2020-11-10T01:23:45Z@",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        // Lower and upper bounds
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "departure_at" => "2020-11-10T01:23:45Z@2020-11-12T01:23:45Z",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        // Upper bound only
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "departure_at" => "@2020-11-12T01:23:45Z",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);

        // Degenerate case when bounds are removed
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "departure_at" => "@",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByCalendarDays()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "calendar_days" => "3",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByLoanableType()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "loanable.type" => "bike",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByOwnerFullName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "loanable.owner.user.full_name" => "David",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByBorrowerFullName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "borrower.user.full_name" => "Georges",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByIncidentStatus()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "incidents.status" => "completed",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByTakeoverStatus()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "takeover.status" => "in_process",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByLoanStatus()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "status" => "completed",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByCurrentStep()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "current_step" => "takeover",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByCommunityId()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "community.id" => "9",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByCommunityName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "community.name" => "Patrie",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByLoanableId()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "loanable.id" => "1",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testFilterLoansByLoanableName()
    {
        $data = [
            "page" => 1,
            "per_page" => 10,
            "fields" => "id,name,last_name,full_name,email",
            "loanable.name" => "Vélo",
        ];
        $response = $this->json("GET", "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(TestCase::$collectionResponseStructure);
    }

    public function testCreateLoans()
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

        $data = [
            "departure_at" => now()->toDateTimeString(),
            "duration_in_minutes" => $this->faker->randomNumber(4),
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "salut",
            "community_id" => $community->id,
        ];

        $response = $this->json("POST", "/api/v1/loans", $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$getLoanResponseStructure);
    }

    public function testCreateLoanOnApprovedCommunityOnly()
    {
        $approvedCommunity = factory(Community::class)->create();
        $suspendedCommunity = factory(Community::class)->create();
        $justRegisteredCommunity = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user->communities()->attach($approvedCommunity->id, [
            "approved_at" => new \DateTime(),
        ]);
        $user->communities()->attach($suspendedCommunity->id, [
            "approved_at" => new \DateTime(),
            "suspended_at" => new \DateTime(),
        ]);
        $user->communities()->attach($justRegisteredCommunity->id);

        $this->actAs($user);

        $borrower = factory(Borrower::class)->create(["user_id" => $user->id]);

        $this->user->communities()->attach($approvedCommunity->id, [
            "approved_at" => new \DateTime(),
        ]);
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $loanable = factory(Bike::class)->create(["owner_id" => $owner->id]);

        $departure = new \Carbon\Carbon();
        $baseData = [
            "duration_in_minutes" => 60,
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "salut",
        ];

        // Try creating a loan on...
        // 1. an approved community
        // 2. a suspended community
        // 3. a new community
        $approvedData = array_merge($baseData, [
            "community_id" => $approvedCommunity->id,
            "departure_at" => $departure->add(2, "hour")->toDateTimeString(),
        ]);
        $suspendedData = array_merge($baseData, [
            "community_id" => $suspendedCommunity->id,
            "departure_at" => $departure->add(2, "hour")->toDateTimeString(),
        ]);
        $justRegisteredData = array_merge($baseData, [
            "community_id" => $justRegisteredCommunity->id,
            "departure_at" => $departure->add(2, "hour")->toDateTimeString(),
        ]);

        $this->json("POST", "/api/v1/loans", $approvedData)->assertStatus(201);
        $this->json("POST", "/api/v1/loans", $suspendedData)->assertStatus(422);
        $this->json("POST", "/api/v1/loans", $justRegisteredData)->assertStatus(
            422
        );

        // Approve previously suspended or not approved communities
        $user->communities()->updateExistingPivot($suspendedCommunity->id, [
            "approved_at" => new \DateTime(),
            "suspended_at" => null,
        ]);
        $user
            ->communities()
            ->updateExistingPivot($justRegisteredCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $this->json("POST", "/api/v1/loans", $suspendedData)->assertStatus(201);
        $this->json("POST", "/api/v1/loans", $justRegisteredData)->assertStatus(
            201
        );
    }

    public function testShowLoans()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $loan = factory(Loan::class)->create(["borrower_id" => $borrower->id]);

        $response = $this->json("GET", "/api/v1/loans/$loan->id");

        $response
            ->assertStatus(200)
            ->assertJson(["id" => $loan->id])
            ->assertJsonStructure(static::$getLoanResponseStructure);
    }

    public function testUpdateLoans()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $community = factory(Community::class)->create();
        $this->user
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $loanable = factory(Bike::class)->create(["owner_id" => $owner->id]);
        $loan = factory(Loan::class)->create([
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
        ]);
        $data = [
            "duration_in_minutes" => $this->faker->randomNumber(
                $nbDigits = 4,
                $strict = false
            ),
        ];

        $response = $this->json("PUT", "/api/v1/loans/$loan->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteLoans()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $loan = factory(Loan::class)->create(["borrower_id" => $borrower->id]);

        $response = $this->json("DELETE", "/api/v1/loans/$loan->id");
        $response->assertStatus(200);

        $response = $this->json("GET", "/api/v1/loans/$loan->id");
        $response->assertStatus(404);
    }

    public function testListLoans()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $loans = factory(Loan::class, 2)
            ->create(["borrower_id" => $borrower->id])
            ->map(function ($loan) {
                return $loan->only(static::$getLoanResponseStructure);
            });

        $response = $this->json("GET", "/api/v1/loans");

        $response
            ->assertStatus(200)
            ->assertJson(["total" => 2])
            ->assertJsonStructure(
                $this->buildCollectionStructure(
                    static::$getLoanResponseStructure
                )
            );
    }

    public function testShowLoansBorrower()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $loan = factory(Loan::class)->create(["borrower_id" => $borrower->id]);

        $response = $this->json("GET", "/api/v1/loans/$loan->id/borrower");

        $response->assertStatus(200)->assertJson(["id" => $borrower->id]);
    }

    public function testCannotCreateConcurrentLoans()
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

        $departure = new \Carbon\Carbon();
        $departure->setSeconds(0);
        $departure->setMilliseconds(0);

        $data = [
            "departure_at" => $departure->toDateTimeString(),
            "duration_in_minutes" => 60,
            "estimated_distance" => 0,
            "estimated_insurance" => 0,
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "salut",
            "community_id" => $community->id,
        ];

        // First loan
        $response = $this->json(
            "POST",
            "/api/v1/loans?fields=*,actions.*",
            $data
        );

        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$getLoanResponseStructure);

        $loanId = $response->json()["id"];
        $intention = $response->json()["actions"][0];

        // Confirm intention on first loan
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loanId/actions/{$intention["id"]}/complete",
            array_merge($intention, ["status" => "completed"])
        );
        $response->assertStatus(200);

        // Exactly the same time: overlap
        $response = $this->json("POST", "/api/v1/loans", $data);

        $response->assertStatus(422)->assertJson([
            "errors" => [
                "loanable_id" => [
                    "Le véhicule n'est pas disponible sur cette période.",
                ],
            ],
        ]);

        // 1 hour from 30 minutes later: overlap
        $response = $this->json(
            "POST",
            "/api/v1/loans",
            array_merge($data, [
                "departure_at" => $departure
                    ->copy()
                    ->add(30, "minutes")
                    ->toDateTimeString(),
            ])
        );

        $response->assertStatus(422)->assertJson([
            "errors" => [
                "loanable_id" => [
                    "Le véhicule n'est pas disponible sur cette période.",
                ],
            ],
        ]);

        // 1 hour from 1 hour later: OK
        $response = $this->json(
            "POST",
            "/api/v1/loans",
            array_merge($data, [
                "departure_at" => $departure
                    ->copy()
                    ->add(60, "minutes")
                    ->toDateTimeString(),
            ])
        );

        $response->assertStatus(201);

        // 1 hour from 30 minutes earlier: overlap
        $response = $this->json(
            "POST",
            "/api/v1/loans",
            array_merge($data, [
                "departure_at" => $departure
                    ->copy()
                    ->subtract(30, "minutes")
                    ->toDateTimeString(),
            ])
        );

        $response->assertStatus(422)->assertJson([
            "errors" => [
                "loanable_id" => [
                    "Le véhicule n'est pas disponible sur cette période.",
                ],
            ],
        ]);

        // 30 minutes from 30 minutes earlier: OK
        $response = $this->json(
            "POST",
            "/api/v1/loans",
            array_merge($data, [
                "departure_at" => $departure
                    ->copy()
                    ->subtract(30, "minutes")
                    ->toDateTimeString(),
                "duration_in_minutes" => 30,
            ])
        );

        $response->assertStatus(201);
    }

    public function testCreateLoansOnlyBuildsOneIntention()
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

        $data = [
            "departure_at" => now()->toDateTimeString(),
            "duration_in_minutes" => $this->faker->randomNumber(4),
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "salut",
            "community_id" => $community->id,
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans?fields=*,actions.*",
            $data
        );

        $response->assertStatus(201);
        $responseData = json_decode($response->getContent());

        // Validate loan actions
        $this->assertEquals(1, count($responseData->actions));

        $refActionStatuses = [
            "intention" => "in_process",
        ];
        $testActionStatuses = [];
        foreach ($responseData->actions as $action) {
            $testActionStatuses[$action->type] = $action->status;
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);
    }

    public function testCreateWithSelfServiceLoanableIsAutomaticallyAccepted()
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
        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "is_self_service" => true,
        ]);

        $data = [
            "departure_at" => now()->toDateTimeString(),
            "duration_in_minutes" => $this->faker->randomNumber(4),
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "salut",
            "community_id" => $community->id,
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans?fields=*,actions.*",
            $data
        );

        $response->assertStatus(201);
        $responseData = json_decode($response->getContent());

        // Validate loan actions
        $this->assertEquals(2, count($responseData->actions));

        $refActionStatuses = [
            "intention" => "completed",
            "pre_payment" => "in_process",
        ];
        $testActionStatuses = [];
        foreach ($responseData->actions as $action) {
            $testActionStatuses[$action->type] = $action->status;
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);
    }

    public function testCreateWithLoanableOnPrivateCommunityIsAutomaticallyAccepted()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);

        $community = factory(Community::class)->create([
            "type" => "borough",
        ]);

        $user = factory(User::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);

        $this->user
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);

        $owner = factory(Owner::class)->create(["user_id" => $user->id]);
        $loanable = factory(Bike::class)->create(["owner_id" => $owner->id]);

        $data = [
            "departure_at" => now()->toDateTimeString(),
            "duration_in_minutes" => 60,
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "salut",
            "community_id" => $community->id,
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans?fields=*,actions.*",
            $data
        );

        $response->assertStatus(201);
        $responseData = json_decode($response->getContent());

        // Validate loan actions
        $this->assertEquals(1, count($responseData->actions));

        $refActionStatuses = [
            "intention" => "in_process",
        ];
        $testActionStatuses = [];
        foreach ($responseData->actions as $action) {
            $testActionStatuses[$action->type] = $action->status;
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Community is private: intentions are automatically accepted
        $community->type = "private";
        $community->save();

        $data["departure_at"] = date("Y-m-d H:i:s", strtotime("tomorrow"));
        $response = $this->json(
            "POST",
            "/api/v1/loans?fields=*,actions.*",
            $data
        );

        $response->assertStatus(201);
        $responseData = json_decode($response->getContent());

        // Validate loan actions
        $this->assertEquals(2, count($responseData->actions));

        $refActionStatuses = [
            "intention" => "completed",
            "pre_payment" => "in_process",
        ];
        $testActionStatuses = [];
        foreach ($responseData->actions as $action) {
            $testActionStatuses[$action->type] = $action->status;
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);
    }

    public function testCreateWithSelfServiceLoanableAndEnoughBalanceAutomaticallyPrePaid()
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
        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
            "is_self_service" => true,
        ]);

        $data = [
            "departure_at" => now()->toDateTimeString(),
            "duration_in_minutes" => $this->faker->randomNumber(4),
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 0,
            "estimated_insurance" => 0,
            "platform_tip" => 0,
            "message_for_owner" => "",
            "reason" => "salut",
            "community_id" => $community->id,
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans?fields=*,actions.*",
            $data
        );

        $response->assertStatus(201);
        $responseData = json_decode($response->getContent());

        // Validate loan actions
        $this->assertEquals(3, count($responseData->actions));

        $refActionStatuses = [
            "intention" => "completed",
            "pre_payment" => "completed",
            "takeover" => "in_process",
        ];
        $testActionStatuses = [];
        foreach ($responseData->actions as $action) {
            $testActionStatuses[$action->type] = $action->status;
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);
    }

    // Basic case: the actual_duration_in_minutes of a loan is its intended duration
    public function testLoanActualDurationInMinutesBase()
    {
        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "duration_in_minutes" => 60,
            ]);

        $this->assertEquals(60, $loan->actual_duration_in_minutes);
        $this->json("GET", "/api/v1/loans/$loan->id")->assertJson([
            "actual_duration_in_minutes" => 60,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => 60,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => 123,
        ])->assertStatus(404);
    }

    // Extended case: the actual_duration_in_minutes is its largest extension duration
    public function testLoanActualDurationInMinutesWithExtension()
    {
        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "duration_in_minutes" => 60,
            ]);

        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "completed",
            ])
        );

        // Reload loan from database before testing
        $loan->refresh();

        $this->assertEquals(120, $loan->actual_duration_in_minutes);
        $this->json("GET", "/api/v1/loans/$loan->id")->assertJson([
            "actual_duration_in_minutes" => 120,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => "110:130",
        ])->assertJson([
            "actual_duration_in_minutes" => 120,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => 121,
        ])->assertStatus(404);
    }

    // Extended case with multiple extensions:
    // same as before, but only the largest completed (approved) extension is considered
    public function testLoanActualDurationInMinutesWithMultipleExtensions()
    {
        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "duration_in_minutes" => 60,
            ]);

        // Only completed extensions are considered
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "completed",
            ])
        );

        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 360,
                "status" => "completed",
            ])
        );

        // In process or canceled extensions are ignored
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 240,
                "status" => "in_process",
            ])
        );
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 480,
                "status" => "canceled",
            ])
        );

        // Reload loan from database before testing
        $loan->refresh();

        $this->assertEquals(360, $loan->actual_duration_in_minutes);
        $this->json("GET", "/api/v1/loans/$loan->id")->assertJson([
            "actual_duration_in_minutes" => 360,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => ":370",
        ])->assertJson([
            "actual_duration_in_minutes" => 360,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => 120,
        ])->assertStatus(404);
    }

    // Paid case: if the loan is paid (payment step is completed),
    // the actual_duration_in_minutes becomes the time when it was paid if its
    // smaller than the extended or regular case, as previously tested
    public function testLoanActualDurationInMinutesWhenPaid()
    {
        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "duration_in_minutes" => 60,
            ]);

        // The loan was completed earlier, so it's assumed the vehicle
        // becomes available earlier
        $payment = $loan->payment()->first();
        $payment->executed_at = Carbon::now()->add(30, "minutes");
        $payment->save();

        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "completed",
            ])
        );

        // In process or canceled extensions are ignored
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 240,
                "status" => "canceled",
            ])
        );

        // Reload loan from database before testing
        $loan->refresh();

        $this->assertEquals(30, $loan->actual_duration_in_minutes);
        $this->json("GET", "/api/v1/loans/$loan->id")->assertJson([
            "actual_duration_in_minutes" => 30,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => "20:",
        ])->assertJson([
            "actual_duration_in_minutes" => 30,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => "40:",
        ])->assertStatus(404);
    }

    // If the loan was paid before it's departure time, then the actual duration is expected to be 0.
    public function testLoanActualDurationInMinutesWhenPaidInThePast()
    {
        // Loan starts 270 minutes ago and was paid 30 minutes before it started.
        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "departure_at" => Carbon::now()->sub(270, "minutes"),
                "duration_in_minutes" => 60,
            ]);

        // The loan was completed earlier, so it's assumed the vehicle
        // becomes available earlier
        $payment = $loan->payment()->first();
        $payment->executed_at = Carbon::now()->sub(300, "minutes");
        $payment->save();

        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "completed",
            ])
        );

        // In process or canceled extensions are ignored
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 240,
                "status" => "canceled",
            ])
        );

        // Reload loan from database before testing
        $loan->refresh();

        $this->assertEquals(0, $loan->actual_duration_in_minutes);
        $this->json("GET", "/api/v1/loans/$loan->id")->assertJson([
            "actual_duration_in_minutes" => 0,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => "0",
        ])->assertJson([
            "actual_duration_in_minutes" => 0,
        ]);
        $this->json("GET", "/api/v1/loans/$loan->id", [
            "actual_duration_in_minutes" => "40:",
        ])->assertStatus(404);
    }
}
