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
    private static $getLoanResponseStructure = [
        'id',
        'departure_at',
        'duration_in_minutes',
        'borrower_id',
        'estimated_distance'
    ];

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

        $baseData = [
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
        ];

        // Try creating a loan on...
        // 1. an approved community
        // 2. a suspended community
        // 3. a new community
        $approvedData = array_merge(
            $baseData,
            [ 'community_id' => $approvedCommunity->id ]
        );
        $suspendedData = array_merge(
            $baseData,
            [ 'community_id' => $suspendedCommunity->id ]
        );
        $justRegisteredData = array_merge(
            $baseData,
            [ 'community_id' => $justRegisteredCommunity->id ]
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
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
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
}
