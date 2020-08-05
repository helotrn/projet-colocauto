<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Extension;
use App\Models\Loan;
use App\Models\Owner;
use Tests\TestCase;

class ExtensionTest extends TestCase
{
    protected $loan;
    protected $loanable;

    public function setUp(): void {
        parent::setUp();

        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $borrower = factory(Borrower::class)->create([ 'user_id' => $this->user->id ]);
        $this->loanable = factory(Bike::class)->create([ 'owner_id' => $owner->id ]);
        $this->loan = factory(Loan::class)->create([
            'loanable_id' => $this->loanable->id,
            'borrower_id' => $borrower->id,
            'duration_in_minutes' => 20,
        ]);
    }

    public function testCreateExtensions() {
        $data = [
            'new_duration' => 30,
            'comments_on_extension' => $this->faker->paragraph,
            'type' => 'extension',
            'status' => 'in_process',
        ];

        $response = $this->json('POST', "/api/v1/loans/{$this->loan->id}/actions", $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testCreateExtensionsForCollectiveLoanable() {
        $this->loanable->owner_id = null;
        $this->loanable->save();

        $data = [
            'new_duration' => 30,
            'comments_on_extension' => $this->faker->paragraph,
            'type' => 'extension',
            'status' => 'in_process',
        ];

        $response = $this->json('POST', "/api/v1/loans/{$this->loan->id}/actions", $data);

        $response->assertStatus(201)->assertJson(array_merge($data, [
            'status' => 'completed',
        ]));
    }
}
