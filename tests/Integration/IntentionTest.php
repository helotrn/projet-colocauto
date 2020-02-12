<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Intention;
use App\Models\Loan;
use Tests\TestCase;

class IntentionTest extends TestCase
{
    private static $getIntentionResponseStructure = [
        'id',
        'executed_at',
        'status',
        'loan_id',
    ];

    public function testCreateIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $data = [
            'executed_at' => now()->toDateTimeString(),
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
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
        $intention = factory(Intention::class)->create(['loan_id' => $loan->id]);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id");

        $response->assertStatus(200)
            ->assertJson(['id' => $intention->id])
            ->assertJsonStructure(static::$getIntentionResponseStructure);
    }

    public function testUpdateIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = factory(Intention::class)->create(['loan_id' => $loan->id]);

        $data = [
            'status' => $this->faker->randomElement(['in_process', 'canceled', 'completed']),
        ];

        $response = $this->json('PUT', "/api/v1/intentions/$intention->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);
        $intention = factory(Intention::class)->create(['loan_id' => $loan->id]);

        $response = $this->json('DELETE', "/api/v1/intentions/$intention->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/intentions/$intention->id");
        $response->assertStatus(404);
    }

    public function testListIntentions() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $loan = factory(Loan::class)->create(['borrower_id' => $borrower->id]);

        $intentions = factory(Intention::class, 2)->create(['loan_id' => $loan->id])->map(function ($intention) {
            return $intention->only(static::$getIntentionResponseStructure);
        });

        $response = $this->json('GET', "/api/v1/intentions");

        $response->assertStatus(200)
            ->assertJson([ 'total' => 2 ])
            ->assertJsonStructure($this->buildCollectionStructure(static::$getIntentionResponseStructure));
    }
}
