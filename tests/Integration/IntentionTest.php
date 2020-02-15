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
        $intention = $loan->intentions()->first();

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
        $intention = $loan->intentions()->first();

        $response = $this->json('GET', "/api/v1/intentions/$intention->id?loan.id=$loan->id");

        $response->assertStatus(200)
            ->assertJson(['id' => $intention->id])
            ->assertJsonStructure(static::$getIntentionResponseStructure);
    }

    public function testUpdateIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = $loan->intentions()->first();

        $data = [
        ];

        $response = $this->json('PUT', "/api/v1/intentions/$intention->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = $loan->intentions()->first();

        $response = $this->json('DELETE', "/api/v1/intentions/$intention->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id");
        $response->assertStatus(404);

        //$response = $this->json('GET', "/api/v1/intentions?loan.id=$loan->id");
        //$response->assertStatus(404);
    }

    public function testCompleteIntentions() {
        $this->markTestIncomplete();
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = $loan->intentions()->first();
        if ($loan->intentions()->count() > 0) {
            $executedAtDate = Carbon::now()->toIsoString();
            Carbon::setTestNow($executedAtDate);

            $response = $this->json('PUT', "/api/v1/loans/$loan->id/actions/$intention->id/complete");
            $response->assertStatus(200);

            $response = $this->json('GET', "/api/v1/intentions/$intention->id?loan.id=$loan->id");
            $response->assertStatus(200)
                ->assertJson(['status' => 'completed'])
                ->assertJson(['executed_at' => $executedAtDate]);
            //TODO fix date formatting
            Carbon::setTestNow();
        } else {
            $response = 'intention error';
        }
    }

    public function testCancelIntentions() {
        $this->markTestIncomplete();
        // Route::put('/loans/{loan_id}/actions/{action_id}/cancel');
    }
}
