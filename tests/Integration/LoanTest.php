<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use Tests\TestCase;

class LoanTest extends TestCase
{
    private static $getLoansResponseStructure = [
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ];

    private static $getLoanResponseStructure = [
        'id',
        'departure_at',
        'duration_in_minutes',
        'borrower_id',
        'estimated_distance'
    ];

    public function testOrderLoansById() {
        $data = [
          'order' => 'id',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,borrower.user.full_name,loanable.owner.user.full_name,community.name',
        ];
        $response = $this->json('GET', "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoansResponseStructure)
            ;
    }

    public function testOrderLoansByDepartureAt() {
        $data = [
          'order' => 'departure_at',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,borrower.user.full_name,loanable.owner.user.full_name,community.name',
        ];
        $response = $this->json('GET', "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoansResponseStructure)
            ;
    }

    public function testOrderLoansByBorrowerFullName() {
        $data = [
          'order' => 'borrower.user.full_name',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,borrower.user.full_name,loanable.owner.user.full_name,community.name',
        ];
        $response = $this->json('GET', "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoansResponseStructure)
            ;
    }

    public function testOrderLoansByOwnerFullName() {
        $data = [
          'order' => 'loanable.owner.user.full_name',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,borrower.user.full_name,loanable.owner.user.full_name,community.name',
        ];
        $response = $this->json('GET', "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoansResponseStructure)
            ;
    }

    public function testOrderLoansByCommunityName() {
        $data = [
          'order' => 'community.name',
          'page' => 1,
          'per_page' => 10,
          'fields' => '*,borrower.user.full_name,loanable.owner.user.full_name,community.name',
        ];
        $response = $this->json('GET', "/api/v1/loans/", $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getLoansResponseStructure)
            ;
    }

    public function testCreateLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $community = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);

        $owner = factory(Owner::class)->create(['user_id' => $user->id]);
        $loanable = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $data = [
            'departure_at' => now()->toDateTimeString(),
            'duration_in_minutes' => $this->faker->randomNumber(4),
            'estimated_distance' => $this->faker->randomNumber(4),
            'estimated_insurance' => $this->faker->randomNumber(4),
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
            'estimated_price' => 1,
            'estimated_insurance' => 1,
            'platform_tip' => 1,
            'message_for_owner' => '',
            'reason' => 'salut',
            'community_id' => $community->id,
        ];

        $response = $this->json('POST', '/api/v1/loans', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(static::$getLoanResponseStructure);
    }

    public function testCreateLoanOnApprovedCommunityOnly() {
        $approvedCommunity = factory(Community::class)->create();
        $suspendedCommunity = factory(Community::class)->create();
        $justRegisteredCommunity = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user->communities()
          ->attach($approvedCommunity->id, [ 'approved_at' => new \DateTime ]);
        $user->communities()
          ->attach($suspendedCommunity->id, [
            'approved_at' => new \DateTime,
            'suspended_at' => new \DateTime,
          ]);
        $user->communities()->attach($justRegisteredCommunity->id);

        $this->actAs($user);

        $borrower = factory(Borrower::class)->create(['user_id' => $user->id]);

        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $loanable = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $departure = new \Carbon\Carbon;
        $baseData = [
            'duration_in_minutes' => 60,
            'estimated_distance' => $this->faker->randomNumber(4),
            'estimated_insurance' => $this->faker->randomNumber(4),
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
            'estimated_price' => 1,
            'estimated_insurance' => 1,
            'platform_tip' => 1,
            'message_for_owner' => '',
            'reason' => 'salut',
        ];

        // Try creating a loan on...
        // 1. an approved community
        // 2. a suspended community
        // 3. a new community
        $approvedData = array_merge(
            $baseData,
            [
                'community_id' => $approvedCommunity->id,
                'departure_at' => $departure->add(2, 'hour')->toDateTimeString(),
            ]
        );
        $suspendedData = array_merge(
            $baseData,
            [
                'community_id' => $suspendedCommunity->id,
                'departure_at' => $departure->add(2, 'hour')->toDateTimeString(),
            ]
        );
        $justRegisteredData = array_merge(
            $baseData,
            [
                'community_id' => $justRegisteredCommunity->id,
                'departure_at' => $departure->add(2, 'hour')->toDateTimeString(),
            ]
        );

        $this->json('POST', '/api/v1/loans', $approvedData)->assertStatus(201);
        $this->json('POST', '/api/v1/loans', $suspendedData)->assertStatus(422);
        $this->json('POST', '/api/v1/loans', $justRegisteredData)->assertStatus(422);

        // Approve previously suspended or not approved communities
        $user->communities()->updateExistingPivot(
            $suspendedCommunity->id,
            [
                'approved_at' => new \DateTime,
                'suspended_at' => null,
            ]
        );
        $user->communities()->updateExistingPivot(
            $justRegisteredCommunity->id,
            [
                'approved_at' => new \DateTime,
            ]
        );

        $this->json('POST', '/api/v1/loans', $suspendedData)->assertStatus(201);
        $this->json('POST', '/api/v1/loans', $justRegisteredData)->assertStatus(201);
    }

    public function testShowLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $response = $this->json('GET', "/api/v1/loans/$loan->id");

        $response->assertStatus(200)
            ->assertJson(['id' => $loan->id])
            ->assertJsonStructure(static::$getLoanResponseStructure);
    }

    public function testUpdateLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $community = factory(Community::class)->create();
        $this->user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $loanable = factory(Bike::class)->create(['owner_id' => $owner->id]);
        $loan = factory(Loan::class)->create([
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
        ]);
        $data = [
            'duration_in_minutes' => $this->faker->randomNumber($nbDigits = 4, $strict = false),
        ];

        $response = $this->json('PUT', "/api/v1/loans/$loan->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $response = $this->json('DELETE', "/api/v1/loans/$loan->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/loans/$loan->id");
        $response->assertStatus(404);
    }

    public function testListLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loans = factory(Loan::class, 2)->create(['borrower_id' => $borrower->id])
            ->map(function ($loan) {
                return $loan->only(static::$getLoanResponseStructure);
            });

        $response = $this->json('GET', "/api/v1/loans");

        $response->assertStatus(200)
            ->assertJson([ 'total' => 2 ])
            ->assertJsonStructure($this->buildCollectionStructure(static::$getLoanResponseStructure));
    }

    public function testShowLoansBorrower() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $response = $this->json('GET', "/api/v1/loans/$loan->id/borrower");

        $response->assertStatus(200)
            ->assertJson([ 'id' => $borrower->id ]);
    }

    public function testCannotCreateConcurrentLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $community = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);

        $owner = factory(Owner::class)->create(['user_id' => $user->id]);
        $loanable = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $departure = new \Carbon\Carbon;
        $departure->setSeconds(0);
        $departure->setMilliseconds(0);

        $data = [
            'departure_at' => $departure->toDateTimeString(),
            'duration_in_minutes' => 60,
            'estimated_distance' => 0,
            'estimated_insurance' => 0,
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
            'estimated_price' => 1,
            'estimated_insurance' => 1,
            'platform_tip' => 1,
            'message_for_owner' => '',
            'reason' => 'salut',
            'community_id' => $community->id,
        ];

        // First loan
        $response = $this->json('POST', '/api/v1/loans', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(static::$getLoanResponseStructure);

        // Exactly the same time: overlap
        $response = $this->json('POST', '/api/v1/loans', $data);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'loanable_id' => ["Le véhicule n'est pas disponible sur cette période."],
                ],
            ]);

        // 1 hour from 30 minutes later: overlap
        $response = $this->json('POST', '/api/v1/loans', array_merge($data, [
            'departure_at' => $departure->copy()->add(30, 'minutes')->toDateTimeString(),
        ]));

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'loanable_id' => ["Le véhicule n'est pas disponible sur cette période."],
                ],
            ]);

        // 1 hour from 1 hour later: OK
        $response = $this->json('POST', '/api/v1/loans', array_merge($data, [
            'departure_at' => $departure->copy()->add(60, 'minutes')->toDateTimeString(),
        ]));

        $response->assertStatus(201);

        // 1 hour from 30 minutes earlier: overlap
        $response = $this->json('POST', '/api/v1/loans', array_merge($data, [
            'departure_at' => $departure->copy()->subtract(30, 'minutes')->toDateTimeString(),
        ]));

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'loanable_id' => ["Le véhicule n'est pas disponible sur cette période."],
                ],
            ]);

        // 30 minutes from 30 minutes earlier: OK
        $response = $this->json('POST', '/api/v1/loans', array_merge($data, [
            'departure_at' => $departure->copy()->subtract(30, 'minutes')->toDateTimeString(),
            'duration_in_minutes' => 30,
        ]));

        $response->assertStatus(201);
    }

    public function testCreateLoansOnlyBuildsOneIntention() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);

        $community = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);

        $owner = factory(Owner::class)->create(['user_id' => $user->id]);
        $loanable = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $data = [
            'departure_at' => now()->toDateTimeString(),
            'duration_in_minutes' => $this->faker->randomNumber(4),
            'estimated_distance' => $this->faker->randomNumber(4),
            'estimated_insurance' => $this->faker->randomNumber(4),
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
            'estimated_price' => 1,
            'estimated_insurance' => 1,
            'platform_tip' => 1,
            'message_for_owner' => '',
            'reason' => 'salut',
            'community_id' => $community->id,
        ];

        $response = $this->json('POST', '/api/v1/loans', $data);

        $response->assertStatus(201);
        $data = json_decode($response->getContent());

        $this->assertEquals(1, count($data->actions));
    }

    public function testCreateWithCollectiveLoanableIsAutomaticallyAccepted() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);

        $community = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);

        $loanable = factory(Bike::class)->create([ 'owner_id' => null ]);

        $data = [
            'departure_at' => now()->toDateTimeString(),
            'duration_in_minutes' => $this->faker->randomNumber(4),
            'estimated_distance' => $this->faker->randomNumber(4),
            'estimated_insurance' => $this->faker->randomNumber(4),
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
            'estimated_price' => 1,
            'estimated_insurance' => 1,
            'platform_tip' => 1,
            'message_for_owner' => '',
            'reason' => 'salut',
            'community_id' => $community->id,
        ];

        $response = $this->json('POST', '/api/v1/loans', $data);

        $response->assertStatus(201);
        $data = json_decode($response->getContent());

        $this->assertEquals(2, count($data->actions));
    }

    public function testCreateWithCollectiveLoanableAndEnoughBalanceAutomaticallyPrePaid() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);

        $community = factory(Community::class)->create();

        $user = factory(User::class)->create();
        $user->communities()->attach($community->id, [ 'approved_at' => new \DateTime ]);

        $loanable = factory(Bike::class)->create([ 'owner_id' => null ]);

        $data = [
            'departure_at' => now()->toDateTimeString(),
            'duration_in_minutes' => $this->faker->randomNumber(4),
            'estimated_distance' => $this->faker->randomNumber(4),
            'estimated_insurance' => $this->faker->randomNumber(4),
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
            'estimated_price' => 0,
            'estimated_insurance' => 0,
            'platform_tip' => 0,
            'message_for_owner' => '',
            'reason' => 'salut',
            'community_id' => $community->id,
        ];

        $response = $this->json('POST', '/api/v1/loans', $data);

        $response->assertStatus(201);
        $data = json_decode($response->getContent());

        $this->assertEquals(3, count($data->actions));
    }
}
