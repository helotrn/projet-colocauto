<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Car;
use App\Models\Intention;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class IntentionTest extends TestCase
{
    private static $getIntentionResponseStructure = [
        'id',
        'loan_id',
    ];

    public function testCompleteIntentions() {
        $loan = $this->buildLoan();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $executedAtDate = Carbon::now()->format('Y-m-d h:m:s');
        Carbon::setTestNow($executedAtDate);

        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
              'type' => 'intention',
            ]
        );
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/loans/$loan->id/actions/$intention->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'completed'])
            ->assertJson(['executed_at' => $executedAtDate]);
    }

    public function testCancelIntentions() {
        $loan = $this->buildLoan();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $executedAtDate = Carbon::now()->format('Y-m-d h:m:s');
        Carbon::setTestNow($executedAtDate);

        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$intention->id/cancel",
            [
                'type' => 'intention',
            ]
        );
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/loans/{$loan->id}/actions/$intention->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'canceled'])
            ->assertJson(['executed_at' => $executedAtDate]);
    }

    private function buildLoan() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create([ 'user_id' => $user ]);
        $loanable = factory(Car::class)->create([ 'owner_id' => $owner ]);

        $loan = factory(Loan::class)->create([
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
        ]);
        $loan = $loan->fresh();

        return $loan;
    }
}
