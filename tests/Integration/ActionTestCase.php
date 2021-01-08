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
use Tests\TestCase;

abstract class ActionTestCase extends TestCase
{
    protected function buildLoan($upTo = null) {
        $community = factory(Community::class)->create();
        $pricing = factory(Pricing::class)->create([
            'community_id' => $community->id,
            'object_type' => 'App\Models\Car',
        ]);

        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create([ 'user_id' => $user ]);

        $loanable = factory(Car::class)->create([
            'owner_id' => $owner,
            'community_id' => $community->id,
        ]);

        $loan = factory(Loan::class)->create([
            'borrower_id' => $borrower->id,
            'loanable_id' => $loanable->id,
            'community_id' => $community->id,
        ]);

        if ($upTo === 'intention') {
            return $loan->fresh();
        }

        $intention = $loan->intention;
        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                'type' => 'intention',
            ]
        );
        $response->assertStatus(200);

        if ($upTo === 'pre_payment') {
            return $loan->fresh();
        }

        $prePayment = $loan->prePayment;
        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$prePayment->id/complete",
            [
                'type' => 'pre_payment',
            ]
        );
        $response->assertStatus(200);

        if ($upTo === 'takeover') {
            return $loan->fresh();
        }

        $takeover = $loan->takeover;
        $response = $this->json(
            'PUT',
            "/api/v1/loans/$loan->id/actions/$takeover->id/complete",
            [
                'type' => 'takeover',
                'mileage_beginning' => 0,
            ]
        );
        $response->assertStatus(200);

        return $loan->fresh();
    }
}
