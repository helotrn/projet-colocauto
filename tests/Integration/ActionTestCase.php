<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Car;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Pricing;
use App\Models\Takeover;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

abstract class ActionTestCase extends TestCase
{
    protected function buildLoan($upTo = null)
    {
        $community = factory(Community::class)->create();
        $pricing = factory(Pricing::class)->create([
            "rule" => "0",
            "community_id" => $community->id,
            "object_type" => "App\Models\Car",
        ]);

        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $user = factory(User::class)->create();
        $user
            ->communities()
            ->attach($community->id, ["approved_at" => Carbon::now()]);

        $owner = factory(Owner::class)->create(["user_id" => $user->id]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $owner,
            "community_id" => $community->id,
            "availability_mode" => "always",
        ]);

        $loanData = [
            "departure_at" => Carbon::now()->toDateTimeString(),
            "duration_in_minutes" => $this->faker->randomNumber(4),
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "Test",
            "community_id" => $community->id,
        ];

        $response = $this->json("POST", "/api/v1/loans", $loanData);

        $response->assertStatus(201);

        // Load newly created loan.
        $loanId = $response->json()["id"];
        $loan = Loan::find($loanId);

        if ($upTo === "intention") {
            return $loan->fresh();
        }

        $intention = $loan->intention;
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                "type" => "intention",
            ]
        );
        $response->assertStatus(200);

        if ($upTo === "pre_payment") {
            return $loan->fresh();
        }

        $prePayment = $loan->prePayment;
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$prePayment->id/complete",
            [
                "type" => "pre_payment",
            ]
        );
        $response->assertStatus(200);

        if ($upTo === "takeover") {
            return $loan->fresh();
        }

        $takeover = $loan->takeover;
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$takeover->id/complete",
            [
                "type" => "takeover",
                "mileage_beginning" => 0,
            ]
        );
        $response->assertStatus(200);

        if ($upTo === "handover") {
            return $loan->fresh();
        }

        $handover = $loan->handover;
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$handover->id/complete",
            [
                "type" => "handover",
                "mileage_end" => 10,
            ]
        );
        $response->assertStatus(200);

        return $loan->fresh();
    }
}
