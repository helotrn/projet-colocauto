<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Loan;
use Tests\TestCase;

class LoanTest extends TestCase
{
    private static $getLoanResponseStructure = [
        'id',
        'departure_at',
        'duration',
        'borrower_id',
        'loanable_type',
        'loanable_id',
    ];

    public function testCreateLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $data = [
            'departure_at' => now(),
            'duration' => $this->faker->randomNumber($nbDigits = null, $strict = false),
            'borrower_id' => $borrower->id,
        ];

        $response = $this->json('POST', "/api/v1/loans", $data);

        $response->assertStatus(201)
                ->assertJsonStructure(static::$getLoanResponseStructure);
    }

    public function testShowLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $response = $this->json('GET', "/api/v1/loans/$loan->id");

        $response->assertStatus(200)->assertJson(['id' => $loan->id]);
    }

    public function testUpdateLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $data = [
            'duration' => $this->faker->randomNumber($nbDigits = 4, $strict = false),
        ];

        $response = $this->json('PUT', "/api/v1/loans/$loan->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteLoans() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $response = $this->json('DELETE', "/api/v1/loans/$loan->id");

        $response->assertStatus(200);
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
}
