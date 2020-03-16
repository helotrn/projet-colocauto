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

    public function testCreateIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $data = [
            'loan_id' => $loan->id,
        ];

        $response = $this->json('POST', "/api/v1/intentions", $data);

        $response->assertStatus(201)
            ->assertJson(['loan_id' => $loan->id])
            ->assertJsonStructure(static::$getIntentionResponseStructure);
    }

    public function testShowIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id?loan.id=$loan->id");

        $response->assertStatus(200)
            ->assertJson(['id' => $intention->id])
            ->assertJsonStructure(static::$getIntentionResponseStructure);
    }

    public function testUpdateIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $data = [];

        $response = $this->json('PUT', "/api/v1/intentions/$intention->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $response = $this->json('DELETE', "/api/v1/intentions/$intention->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id");
        $response->assertStatus(404);

        $response = $this->json('GET', "/api/v1/intentions?loan.id=$loan->id");
        $response->assertJson([ 'total' => 0 ]);
    }

    public function testCompleteIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $loan = $loan->fresh();

        $intention = $loan->intention;

        $this->assertNotNull($intention);

        $executedAtDate = substr(Carbon::now('-4')->format("Y-m-d h:m:sO"), 0, -2);
        Carbon::setTestNow($executedAtDate);

        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$intention->id/complete"
        );
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id?loan.id=$loan->id");
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

        $executedAtDate = substr(Carbon::now('-4')->format("Y-m-d h:m:sO"), 0, -2);
        Carbon::setTestNow($executedAtDate);

        $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/cancel");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id?loan.id=$loan->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'canceled'])
            ->assertJson(['executed_at' => $executedAtDate]);
    }
}
