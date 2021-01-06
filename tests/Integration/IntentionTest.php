<?php

namespace Tests\Integration;

use Carbon\Carbon;

class IntentionTest extends ActionTestCase
{
    private static $getIntentionResponseStructure = [
        'id',
        'loan_id',
    ];

    public function testCompleteIntentions() {
        $loan = $this->buildLoan('intention');

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
        $loan = $this->buildLoan('intention');

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
}
