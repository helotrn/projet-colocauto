<?php

namespace Tests\Integration;

use Carbon\Carbon;

class PrePaymentTest extends ActionTestCase
{
    public function testCreatePrePaymentsWithActionsFlow()
    {
        $loan = $this->buildLoan("intention");

        $intention = $loan->intention;

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                "type" => "intention",
            ]
        );
        $response->assertStatus(200);

        $prePayment = $loan->prePayment;
        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$prePayment->id"
        );

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertEquals($prePayment->id, array_get($json, "id"));
        $this->assertEquals("in_process", array_get($json, "status"));
    }

    public function testCompletePrePayments()
    {
        $loan = $this->buildLoan("pre_payment");

        $executedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($executedAtDate);

        $prePayment = $loan->prePayment;

        $this->assertNotNull($prePayment);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$prePayment->id/complete",
            [
                "type" => "pre_payment",
            ]
        );
        $response->assertStatus(200);

        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$prePayment->id"
        );
        $response
            ->assertStatus(200)
            ->assertJson(["status" => "completed"])
            ->assertJson(["executed_at" => $executedAtDate]);

        $loan = $loan->fresh();

        $takeover = $loan->takeover;

        $this->assertNotNull($takeover);
    }

    public function testCancelPrePayments()
    {
        $loan = $this->buildLoan("pre_payment");

        $executedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($executedAtDate);

        $intention = $loan->intention;
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                "type" => "intention",
            ]
        );
        $response->assertStatus(200);

        $loan = $loan->fresh();

        $prePayment = $loan->prePayment;

        $this->assertNotNull($prePayment);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$prePayment->id/cancel",
            [
                "type" => "pre_payment",
            ]
        );
        $response->assertStatus(200);

        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$prePayment->id"
        );
        $response
            ->assertStatus(200)
            ->assertJson(["status" => "canceled"])
            ->assertJson(["executed_at" => $executedAtDate]);
    }
}
