<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\PrePayment;
use App\Models\Pricing;
use App\Models\User;
use MStaack\LaravelPostgis\Geometries\Point;
use Tests\TestCase;

class BikeTest extends TestCase
{
    private static $getBikeResponseStructure = [
        'id',
        'name',
        'bike_type',
        'model',
        'size',
        'position',
        'location_description',
        'instructions',
        'comments',
    ];

    public function testCreateBikes() {
        $data = [
            'name' => $this->faker->name,
            'position' => [$this->faker->latitude, $this->faker->longitude],
            'location_description' => $this->faker->sentence,
            'comments' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph,
            'model' => $this->faker->sentence,
            'bike_type' => $this->faker->randomElement(['regular' ,'electric', 'fixed_wheel']),
            'size' => $this->faker->randomElement(['big' ,'medium', 'small', 'kid']),
            'type' => 'bike',
        ];

        $response = $this->json('POST', '/api/v1/bikes', $data);
        $response->assertStatus(201)
            ->assertJsonStructure(static::$getBikeResponseStructure);
    }

    public function testShowBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bike = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('GET', "/api/v1/bikes/$bike->id");

        $response->assertStatus(200)
            ->assertJsonStructure(static::$getBikeResponseStructure);
    }

    public function testUpdateBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bike = factory(Bike::class)->create(['owner_id' => $owner->id]);
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', "/api/v1/bikes/$bike->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bike = factory(Bike::class)->create(['owner_id' => $owner->id]);

        $response = $this->json('DELETE', "/api/v1/bikes/$bike->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/bikes/$bike->id");
        $response->assertStatus(404);
    }

    public function testDeleteBikesWithActiveLoan() {
        // No active loan
        $loan = $this->buildLoan();
        $bike = $loan->loanable;

        $response = $this->json('DELETE', "/api/v1/bikes/$bike->id");
        $response->assertStatus(200);

        $response = $this->json('GET', "/api/v1/bikes/$bike->id");
        $response->assertStatus(404);

        // Prepaid (active) loan
        $loan = $this->buildLoan();
        $prePayment = factory(PrePayment::class)->create([
          'loan_id' => $loan->id,
          'status' => 'completed',
        ]);
        $bike = $loan->loanable;
        $loan = $loan->fresh();

        $response = $this->json('DELETE', "/api/v1/bikes/$bike->id");
        $response->assertStatus(422)
          ->assertJson([
            'errors' => [
              'id' => [ 'Ce véhicule a des emprunts en cours.' ],
            ],
          ]);

        // Only completed loan
        $loan = $this->buildLoan();
        $prePayment = factory(PrePayment::class)->create([
          'loan_id' => $loan->id,
          'status' => 'completed',
        ]);
        $payment = factory(Payment::class)->create([
          'loan_id' => $loan->id,
          'status' => 'completed',
        ]);
        $bike = $loan->loanable;
        $loan = $loan->fresh();

        $response = $this->json('DELETE', "/api/v1/bikes/$bike->id");
        $response->assertStatus(200);
    }

    public function testListBikes() {
        $owner = factory(Owner::class)->create(['user_id' => $this->user->id]);
        $bikes = factory(Bike::class, 2)->create(['owner_id' => $owner->id])
            ->map(function ($bike) {
                return $bike->only(static::$getBikeResponseStructure);
            });

        $response = $this->json('GET', '/api/v1/bikes');

        $response->assertStatus(200)
            ->assertJson([ 'total' => 2 ])
            ->assertJsonStructure(
                $this->buildCollectionStructure(static::$getBikeResponseStructure)
            );
    }

    protected function buildLoan($upTo = null) {
        $community = factory(Community::class)->create();
        $pricing = factory(Pricing::class)->create([
            'community_id' => $community->id,
            'object_type' => 'App\Models\Bike',
        ]);

        $borrower = factory(Borrower::class)->create(['user_id' => $this->user->id]);
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create([ 'user_id' => $user ]);

        $loanable = factory(Bike::class)->create([
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

        return $loan->fresh();
    }
}
