<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Intention;
use App\Models\Loan;
use Carbon\Carbon;
use Tests\TestCase;

class IntentionTest extends TestCase
{
    private static $getIntentionResponseStructure = [
        'id',
        'loan_id',
    ];

    public function testCompleteIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $executedAtDate = substr(Carbon::now()->format("Y-m-d h:m:sO"), 0, -2);
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
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $executedAtDate = substr(Carbon::now()->format("Y-m-d h:m:sO"), 0, -2);
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
}
