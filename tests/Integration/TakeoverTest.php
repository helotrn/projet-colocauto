<?php

namespace Tests\Integration;

use Carbon\Carbon;

class TakeoverTest extends ActionTestCase
{
    public function testCreateTakeoversWithActionsFlow()
    {
        $loan = $this->buildLoan("takeover");

        $takeover = $loan->takeover;
        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$takeover->id"
        );

        $json = $response->json();
        $this->assertEquals($takeover->id, array_get($json, "id"));
        $this->assertEquals("in_process", array_get($json, "status"));
    }

    public function testCompleteTakeovers()
    {
        $executedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($executedAtDate);

        $loan = $this->buildLoan("takeover");

        $takeover = $loan->takeover;

        $this->assertNotNull($takeover);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$takeover->id/complete",
            [
                "type" => "takeover",
                "mileage_beginning" => 0,
            ]
        );
        $response->assertStatus(200);

        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$takeover->id"
        );
        $response
            ->assertStatus(200)
            ->assertJson(["status" => "completed"])
            ->assertJson(["executed_at" => $executedAtDate]);

        $loan = $loan->fresh();

        $takeover = $loan->takeover;

        $this->assertNotNull($takeover);
    }

    public function testCancelTakeovers()
    {
        $executedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($executedAtDate);

        $loan = $this->buildLoan("takeover");

        $takeover = $loan->takeover;

        $this->assertNotNull($takeover);

        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$takeover->id/cancel",
            [
                "type" => "takeover",
            ]
        );
        $response->assertStatus(200);

        $response = $this->json(
            "GET",
            "/api/v1/loans/$loan->id/actions/$takeover->id"
        );
        $response
            ->assertStatus(200)
            ->assertJson(["status" => "canceled"])
            ->assertJson(["executed_at" => $executedAtDate]);
    }
}
