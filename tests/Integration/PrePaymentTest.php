<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\PrePayment;
use Carbon\Carbon;
use Tests\TestCase;

class PrePaymentTest extends TestCase
{
    public function testCreatePrePaymentsWithActionsFlow() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();
        $intention = $loan->intention;

        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/complete");
        $response->assertStatus(200);

        $prePayment = $loan->prePayment;
        $response = $this->json('GET', "/api/v1/pre_payments?loan.id=$loan->id");

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertEquals($prePayment->id, array_get($json, 'data.0.id'));
    }

    public function testCompletePrePayments() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $executedAtDate = substr(Carbon::now('-4')->format("Y-m-d h:m:sO"), 0, -2);
        Carbon::setTestNow($executedAtDate);

        $intention = $loan->intention;
        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/complete");
        $response->assertStatus(200);

        $loan = $loan->fresh();

        $prePayment = $loan->prePayment;

        $this->assertNotNull($prePayment);

        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$prePayment->id/complete");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/pre_payments/$prePayment->id?loan.id=$loan->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'completed'])
            ->assertJson(['executed_at' => $executedAtDate]);

        $loan = $loan->fresh();

        $takeover = $loan->takeover;

        $this->assertNotNull($takeover);
    }

    public function testCancelPrePayments() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $executedAtDate = substr(Carbon::now('-4')->format("Y-m-d h:m:sO"), 0, -2);
        Carbon::setTestNow($executedAtDate);

        $intention = $loan->intention;
        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/complete");
        $response->assertStatus(200);

        $loan = $loan->fresh();

        $prePayment = $loan->prePayment;

        $this->assertNotNull($prePayment);

        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$prePayment->id/cancel");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/pre_payments/$prePayment->id?loan.id=$loan->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'canceled'])
            ->assertJson(['executed_at' => $executedAtDate]);
    }
}
