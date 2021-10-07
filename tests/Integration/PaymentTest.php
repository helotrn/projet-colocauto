<?php

namespace Tests\Integration;

use Carbon\Carbon;

class PaymentTest extends ActionTestCase
{
    public function testCompletePayments()
    {
        $loan = $this->buildLoan("payment");

        $executedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($executedAtDate);

        $payment = $loan->payment;

        $this->assertNotNull($payment);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$payment->id/complete",
            [
                "type" => "payment",
                "platform_tip" => 0,
            ]
        );
        $response->assertStatus(200);

        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$payment->id"
        );
        $response
            ->assertStatus(200)
            ->assertJson(["status" => "completed"])
            ->assertJson(["executed_at" => $executedAtDate]);

        $loan = $loan->fresh();

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$payment->id/complete",
            [
                "type" => "payment",
            ]
        )->assertStatus(422);
    }
}
