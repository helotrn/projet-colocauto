<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class LoanableTest extends TestCase
{
    public function testRetrieveNextLoan() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create(['user_id' => $user->id]);
        $loanable = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $departure = new Carbon;

        $data = factory(Loan::class)->make([
            'duration_in_minutes' => 30,
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
        ])->toArray();

        $response = $this->json('POST', '/api/v1/loans', array_merge($data, [
            'departure_at' => $departure->add(1, 'hour')->toDateTimeString(),
        ]));
        $response->assertStatus(201);
        $nextLoanId = $response->json()['id'];

        $response = $this->json('POST', '/api/v1/loans', array_merge($data, [
            'departure_at' => $departure->add(2, 'hour')->toDateTimeString(),
        ]));
        $response->assertStatus(201);

        $response = $this->json('POST', '/api/v1/loans', array_merge($data, [
            'departure_at' => $departure->subtract(4, 'hour')->toDateTimeString(),
        ]));
        $response->assertStatus(201);

        $now = new Carbon;
        $response = $this->json('GET', "/api/v1/loanables/{$loanable->id}/loans", [
            'order' => 'departure_at',
            'departure_at' => $now->format('Y-m-d H:i:s') . ':',
            'per_page' => 1,
        ]);
        $response->dump()->assertStatus(200)
            ->assertJson([ 'data' => [
                [
                    'id' => $nextLoanId,
                ],
            ]]);

        // Shortcut request
        $response = $this->json('GET', "/api/v1/loanables/{$loanable->id}/loans/next");
        $response->assertStatus(200)
            ->assertJson([
                'id' => $nextLoanId,
            ]);
    }
}
