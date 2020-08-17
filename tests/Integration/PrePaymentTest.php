<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Car;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\PrePayment;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PrePaymentTest extends TestCase
{
    public function testCreatePrePaymentsWithActionsFlow() {
        $loan = $this->buildLoan();

        $intention = $loan->intention;

        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                'type' => 'intention',
            ]
        );
        $response->assertStatus(200);

        $prePayment = $loan->prePayment;
        $response = $this->json('GET', "/api/v1/loans/$loan->id/actions/$prePayment->id");

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertEquals($prePayment->id, array_get($json, 'id'));
        $this->assertEquals('in_process', array_get($json, 'status'));
    }

    public function testCompletePrePayments() {
        $loan = $this->buildLoan();

        $executedAtDate = Carbon::now()->format('Y-m-d h:m:s');
        Carbon::setTestNow($executedAtDate);

        $intention = $loan->intention;
        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                'type' => 'intention',
            ]
        );
        $response->assertStatus(200);

        $loan = $loan->fresh();

        $prePayment = $loan->prePayment;

        $this->assertNotNull($prePayment);

        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$prePayment->id/complete",
            [
                'type' => 'pre_payment',
            ]
        );
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/loans/$loan->id/actions/$prePayment->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'completed'])
            ->assertJson(['executed_at' => $executedAtDate]);

        $loan = $loan->fresh();

        $takeover = $loan->takeover;

        $this->assertNotNull($takeover);
    }

    public function testCancelPrePayments() {
        $loan = $this->buildLoan();

        $executedAtDate = Carbon::now()->format('Y-m-d h:m:s');
        Carbon::setTestNow($executedAtDate);

        $intention = $loan->intention;
        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                'type' => 'intention',
            ]
        );
        $response->assertStatus(200);

        $loan = $loan->fresh();

        $prePayment = $loan->prePayment;

        $this->assertNotNull($prePayment);

        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$prePayment->id/cancel",
            [
                'type' => 'pre_payment',
            ]
        );
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/loans/$loan->id/actions/$prePayment->id");
        $response->assertStatus(200)
            ->assertJson(['status' => 'canceled'])
            ->assertJson(['executed_at' => $executedAtDate]);
    }

    private function buildLoan() {
        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create([ 'user_id' => $user ]);
        $loanable = factory(Car::class)->create([ 'owner_id' => $owner ]);

        $loan = factory(Loan::class)->create([
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
        ]);
        $loan = $loan->fresh();

        return $loan;
    }
}
