<?php

namespace Tests\Integration;

use Carbon\Carbon;
use App\Models\Borrower;
use App\Models\Car;
use App\Models\Community;
use App\Models\Handover;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Pricing;
use App\Models\Takeover;
use App\Models\User;
use Tests\TestCase;

class HandoverTest extends ActionTestCase
{
    public function testCreateHandoversWithActionsFlow()
    {
        $loan = $this->buildLoan("handover");

        $handover = $loan->handover;
        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$handover->id"
        );

        $json = $response->json();
        $this->assertEquals($handover->id, array_get($json, "id"));
        $this->assertEquals("in_process", array_get($json, "status"));
    }

    public function testCompleteHandovers()
    {
        $executedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($executedAtDate);

        $loan = $this->buildLoan("handover");

        $handover = $loan->handover;

        $this->assertNotNull($handover);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$handover->id/complete",
            [
                "type" => "handover",
                "mileage_end" => 0,
            ]
        );
        $response->assertStatus(200);

        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$handover->id"
        );
        $response
            ->assertStatus(200)
            ->assertJson(["status" => "completed"])
            ->assertJson(["executed_at" => $executedAtDate]);

        $loan = $loan->fresh();

        $handover = $loan->handover;

        $this->assertNotNull($handover);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$handover->id/complete",
            [
                "type" => "handover",
                "mileage_end" => 0,
            ]
        );
        $response->assertStatus(422);
    }

    public function testCancelHandovers()
    {
        $executedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($executedAtDate);

        $loan = $this->buildLoan("handover");

        $handover = $loan->handover;

        $this->assertNotNull($handover);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$handover->id/cancel",
            [
                "type" => "handover",
            ]
        );
        $response->assertStatus(200);

        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$handover->id"
        );
        $response
            ->assertStatus(200)
            ->assertJson(["status" => "canceled"])
            ->assertJson(["executed_at" => $executedAtDate]);
    }
}
