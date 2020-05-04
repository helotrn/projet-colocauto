<?php

namespace Tests\Integration;

use App\Models\Community;
use App\Models\Car;
use App\Models\Owner;
use App\Models\Pricing;
use Tests\TestCase;

class PricingTest extends TestCase
{
    public function testEvaluatePricing() {
        $community = factory(Community::class)->create();

        $syncCommunities = [];
        $syncCommunities[$community->id] = [
            'approved_at' => new \DateTime(),
            'role' => 'admin',
        ];
        $this->user->communities()->sync($syncCommunities);

        $owner = factory(Owner::class)->create([ 'user_id' => $this->user->id ]);
        $car = factory(Car::class)->create([
            'owner_id' => $owner->id ,
            'year_of_circulation' => 1000000,
        ]);

        $pricing = factory(Pricing::class)->create([
            'community_id' => $community->id,
            'rule' => '$KM * 1 + $MINUTES * 1000 + $OBJET.year_of_circulation '
                . '+ $EMPRUNT.days * 0.1',
        ]);

        $data = [
            'km' => 1,
            'minutes' => 1,
            'loanable' => $car->toArray(),
            'loan' => [
                'days' => 2,
            ],
        ];

        $response = $this->json('PUT', route('pricings.evaluate', $pricing->id), $data);

        $response->assertStatus(200)->assertJson([ 'price' => 1001001.2 ]);
    }
}
