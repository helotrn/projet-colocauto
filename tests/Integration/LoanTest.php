<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
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
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create(['user_id' => $user->id]);
        $loanable = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $data = [
            'departure_at' => now()->toDateTimeString(),
            'duration_in_minutes' => $this->faker->randomNumber($nbDigits = null, $strict = false),
            'estimated_distance' => $this->faker->randomNumber($nbDigits = null, $strict = false),
            'borrower_id' => $borrower->id,
            'loanable_type' => 'bike',
            'loanable_id' => $loanable->id,
            'estimated_price' => 1,
            'message_for_owner' => '',
            'reason' => 'salut',
        ];

        $response = $this->json('POST', "/api/v1/loans", $data);

        $response->assertStatus(201)
                ->assertJsonStructure(static::$getLoanResponseStructure);
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

        $data = [
            'loan_id' => $loan->id,
        ];
        $response = $this->json('GET', "/api/v1/borrowers/$borrower->id", $data);

        $response->assertStatus(200)
            ->assertJson([ 'id' => $borrower->id ]);
    }

    public function testShowLoansLoanable() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $data = [
            'loan_id' => $loan->id,
        ];
        $response = $this->json('GET', "/api/v1/borrowers/$borrower->id", $data);

        $response->assertStatus(200)
            ->assertJson([ 'id' => $borrower->id ]);
    }
}
