<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class BorrowerTest extends TestCase
{
    public function testApproveBorrowers() {
        $borrower = factory(Borrower::class)->create([ 'user_id' => $this->user->id ]);

        $response = $this->json('GET', "/api/v1/users/{$this->user->id}/borrower");
        $response->assertStatus(200)->assertJson(['approved_at' => null]);

        $approvedAtDate = Carbon::now();
        Carbon::setTestNow($approvedAtDate);

        $response = $this->json('PUT', "/api/v1/users/{$this->user->id}/borrower/approve");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/users/{$this->user->id}/borrower");
        $response->assertStatus(200)->assertJson(['approved_at' => $approvedAtDate]);
    }
}
