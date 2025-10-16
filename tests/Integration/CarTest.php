<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Car;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\PrePayment;
use App\Models\Takeover;
use App\Models\Handover;
use App\Models\Pricing;
use App\Models\User;
use Carbon\Carbon;
use MStaack\LaravelPostgis\Geometries\Point;
use Tests\TestCase;

class CarTest extends TestCase
{
    private static $carResponseStructure = [
        "brand",
        "comments",
        "engine",
        "has_informed_insurer",
        "instructions",
        "insurer",
        "is_value_over_fifty_thousand",
        "location_description",
        "model",
        "name",
        "papers_location",
        "plate_number",
        "transmission_mode",
        "year_of_circulation",
        "cost_per_km",
        "cost_per_month",
        "owner_compensation",
    ];

    private function getCarCreationData($owner)
    {
        return [
            "brand" => $this->faker->word,
            "comments" => $this->faker->paragraph,
            "engine" => $this->faker->randomElement([
                "fuel",
                "diesel",
                "electric",
                "hybrid",
            ]),
            "has_informed_insurer" => true,
            "instructions" => $this->faker->paragraph,
            "insurer" => $this->faker->word,
            "is_value_over_fifty_thousand" => $this->faker->boolean,
            "location_description" => $this->faker->sentence,
            "model" => $this->faker->sentence,
            "name" => $this->faker->name,
            "owner_id" => $owner->id,
            "papers_location" => $this->faker->randomElement([
                "in_the_car",
                "to_request_with_car",
            ]),
            "plate_number" => $this->faker->shuffle("9F29J2"),
            "pricing_category" => "large",
            "transmission_mode" => $this->faker->randomElement([
                "automatic",
                "manual",
            ]),
            "type" => "car",
            "year_of_circulation" => $this->faker->year($max = "now"),
            "cost_per_km" => 1.00,
            "cost_per_month" => 2.00,
            "owner_compensation" => 3.00,
        ];
    }

    public function testCreateCars()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $data = $this->getCarCreationData($owner);

        $response = $this->json("POST", "/api/v1/cars", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$carResponseStructure)
            ->assertJson(["pricing_category" => "large"]);
    }

    public function testUserCannotCreateCars()
    {
        $community = factory(Community::class)->create();
        $user = factory(User::class)->states("withOwner")->create();
        $user->communities()->attach($community, ["approved_at" => new Carbon()]);

        $admin_user = factory(User::class)->create();
        $admin_user->role = "community_admin";
        $admin_user->save();
        $admin_user->administrableCommunities()->attach($community);

        $this->actAs($user);

        $data = $this->getCarCreationData($user->owner);

        $response = $this->json("POST", "/api/v1/cars", $data);
        $response->assertStatus(403);
    }

    public function testUserWithNonManagedCommunityCanCreateCarsInTheCommunity()
    {
        $this->withoutEvents();
        $community = factory(Community::class)->create();
        $user = factory(User::class)->states("withOwner")->create();
        $user->communities()->attach($community, [
            "approved_at" => new Carbon(),
            "role" => "responsible",
        ]);

        $this->actAs($user);

        $data = $this->getCarCreationData($user->owner);
        $data['community'] = $community;

        $response = $this->json("POST", "/api/v1/cars", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$carResponseStructure);
    }

    public function testShowCars()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $car = factory(Car::class)->create(["owner_id" => $owner->id]);

        $response = $this->json("GET", "/api/v1/cars/$car->id");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$carResponseStructure);
    }

    public function testUpdateCars()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $car = factory(Car::class)->create(["owner_id" => $owner->id]);
        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json("PUT", "/api/v1/cars/$car->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteCars()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $car = factory(Car::class)->create(["owner_id" => $owner->id]);

        $response = $this->json("DELETE", "/api/v1/cars/$car->id");
        $response->assertStatus(200);

        $response = $this->json("GET", "/api/v1/cars/$car->id");
        $response->assertStatus(404);
    }

    public function testDeleteCarsWithActiveLoan()
    {
        // No active loan
        $loan = $this->buildLoan();
        $car = $loan->loanable;

        $response = $this->json("DELETE", "/api/v1/cars/$car->id");
        $response->assertStatus(200);

        $response = $this->json("GET", "/api/v1/cars/$car->id");
        $response->assertStatus(404);

        // Prepaid (active) loan
        $loan = $this->buildLoan();
        $prePayment = factory(PrePayment::class)->create([
            "loan_id" => $loan->id,
            "status" => "completed",
        ]);
        $car = $loan->loanable;
        $loan = $loan->fresh();

        $response = $this->json("DELETE", "/api/v1/cars/$car->id");
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "id" => ["Ce véhicule a des emprunts en cours."],
            ],
        ]);

        // Only completed loan
        $loan = $this->buildLoan();
        $takeover = factory(Takeover::class)->create([
            "loan_id" => $loan->id,
            "status" => "completed",
        ]);
        $handover = factory(Handover::class)->create([
            "loan_id" => $loan->id,
            "status" => "completed",
        ]);
        $car = $loan->loanable;
        $loan = $loan->fresh();

        $response = $this->json("DELETE", "/api/v1/cars/$car->id");
        $response->assertStatus(200);
    }

    public function testListCars()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $cars = factory(Car::class, 2)
            ->create(["owner_id" => $owner->id])
            ->map(function ($car) {
                return $car->only(static::$carResponseStructure);
            });

        $response = $this->json("GET", "/api/v1/cars");

        $response
            ->assertStatus(200)
            ->assertJson(["total" => 2])
            ->assertJsonStructure(
                $this->buildCollectionStructure(static::$carResponseStructure)
            );
    }

    protected function buildLoan($upTo = null)
    {
        $community = factory(Community::class)->create();
        $pricing = factory(Pricing::class)->create([
            "community_id" => $community->id,
            "object_type" => "App\Models\Car",
        ]);

        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create(["user_id" => $user]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $owner,
            "community_id" => $community->id,
            "availability_mode" => "always",
        ]);

        $loan = factory(Loan::class)->create([
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "community_id" => $community->id,
        ]);

        $loanData = [
            "departure_at" => Carbon::now()->toDateTimeString(),
            "duration_in_minutes" => $this->faker->randomNumber(4),
            "estimated_distance" => $this->faker->randomNumber(4),
            "estimated_insurance" => $this->faker->randomNumber(4),
            "borrower_id" => $borrower->id,
            "loanable_id" => $loanable->id,
            "estimated_price" => 1,
            "estimated_insurance" => 1,
            "platform_tip" => 1,
            "message_for_owner" => "",
            "reason" => "Test",
            "community_id" => $community->id,
        ];

        $response = $this->json("POST", "/api/v1/loans", $loanData);

        $response->assertStatus(201);

        // Load newly created loan.
        $loanId = $response->json()["id"];
        $loan = Loan::find($loanId);

        if ($upTo === "intention") {
            return $loan->fresh();
        }

        $intention = $loan->intention;
        $response = $this->json(
            "PUT",
            "/api/v1/loans/$loan->id/actions/$intention->id/complete",
            [
                "type" => "intention",
            ]
        );
        $response->assertStatus(200);

        return $loan->fresh();
    }
}
