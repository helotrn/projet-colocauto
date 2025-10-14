<?php

namespace Tests\Integration;

use Carbon\Carbon;

class PaymentTest extends ActionTestCase
{
    public function testCompletePayments()
    {
        $this->markTestSkipped('Payment is not supported anymore');

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

    public function testCompletePayments_failsIfNotEnoughMoney()
    {
        $this->markTestSkipped('Payment is not supported anymore');

        $this->withoutEvents();

        $loan = $this->buildLoan("payment");
        $loan->platform_tip = 5;
        $loan->save();

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/{$loan->payment->id}/complete",
            [
                "type" => "payment",
            ]
        );

        $response->assertStatus(422)->assertJson([
            "errors" => [
                "status" => [
                    "L'emprunteur-se n'a pas assez de fonds dans son solde pour payer présentement.",
                ],
            ],
        ]);
    }

    public function testCompletePayments_failsIfNotValidated()
    {
        $this->markTestSkipped('Payment is not supported anymore');

        $this->withoutEvents();

        $loan = $this->buildLoan("payment");
        $loan->borrower->user->balance = 20;
        $loan->borrower->user->save();

        $pricing = $loan->community->pricings[0];
        $pricing->rule = "5";
        $pricing->save();

        $this->actAs($loan->loanable->owner->user);
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/{$loan->payment->id}/complete",
            [
                "type" => "payment",
                "loan_id" => $loan->id,
            ]
        );
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "status" => ["Le paiement ne peut être complété présentement."],
            ],
        ]);
    }
}
