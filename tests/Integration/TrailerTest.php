<?php

namespace Tests\Integration;

use App\Models\Borrower;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\PrePayment;
use App\Models\Pricing;
use App\Models\Trailer;
use App\Models\User;
use Carbon\Carbon;
use MStaack\LaravelPostgis\Geometries\Point;
use Tests\TestCase;

class TrailerTest extends TestCase
{
    private static $getTrailerResponseStructure = [
        "id",
        "name",
        "comments",
        "instructions",
        "location_description",
        "maximum_charge",
        "dimensions",
        "position",
    ];

    public function testCreateTrailers()
    {
        $data = [
            "name" => $this->faker->name,
            "position" => [$this->faker->latitude, $this->faker->longitude],
            "location_description" => $this->faker->sentence,
            "comments" => $this->faker->paragraph,
            "instructions" => $this->faker->paragraph,
            "maximum_charge" => $this->faker->numberBetween(
                $min = 1000,
                $max = 9000
            ),
            "dimensions" => $this->faker->sentence(2),
            "type" => "trailer",
        ];

        $response = $this->json("POST", "/api/v1/trailers", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$getTrailerResponseStructure);
    }

    public function testShowTrailers()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $trailer = factory(Trailer::class)->create(["owner_id" => $owner->id]);

        $response = $this->json("GET", "/api/v1/trailers/$trailer->id");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getTrailerResponseStructure);
    }

    public function testUpdateTrailers()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $trailer = factory(Trailer::class)->create(["owner_id" => $owner->id]);
        $data = [
            "name" => $this->faker->name,
        ];

        $response = $this->json("PUT", "/api/v1/trailers/$trailer->id", $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteTrailers()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $trailer = factory(Trailer::class)->create(["owner_id" => $owner->id]);

        $response = $this->json("DELETE", "/api/v1/trailers/$trailer->id");
        $response->assertStatus(200);

        $response = $this->json("GET", "/api/v1/trailers/$trailer->id");
        $response->assertStatus(404);
    }

    public function testDeleteTrailersWithActiveLoan()
    {
        // No active loan
        $loan = $this->buildLoan();
        $trailer = $loan->loanable;

        $response = $this->json("DELETE", "/api/v1/trailers/$trailer->id");
        $response->assertStatus(200);

        $response = $this->json("GET", "/api/v1/trailers/$trailer->id");
        $response->assertStatus(404);

        // Prepaid (active) loan
        $loan = $this->buildLoan();
        $prePayment = factory(PrePayment::class)->create([
            "loan_id" => $loan->id,
            "status" => "completed",
        ]);
        $trailer = $loan->loanable;
        $loan = $loan->fresh();

        $response = $this->json("DELETE", "/api/v1/trailers/$trailer->id");
        $response->assertStatus(422)->assertJson([
            "errors" => [
                "id" => ["Ce véhicule a des emprunts en cours."],
            ],
        ]);

        // Only completed loan
        $loan = $this->buildLoan();
        $prePayment = factory(PrePayment::class)->create([
            "loan_id" => $loan->id,
            "status" => "completed",
        ]);
        $payment = factory(Payment::class)->create([
            "loan_id" => $loan->id,
            "status" => "completed",
        ]);
        $trailer = $loan->loanable;
        $loan = $loan->fresh();

        $response = $this->json("DELETE", "/api/v1/trailers/$trailer->id");
        $response->assertStatus(200);
    }
    public function testListTrailers()
    {
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $trailers = factory(Trailer::class, 2)
            ->create(["owner_id" => $owner->id])
            ->map(function ($trailer) {
                return $trailer->only(static::$getTrailerResponseStructure);
            });

        $response = $this->json("GET", "/api/v1/trailers");

        $response
            ->assertStatus(200)
            ->assertJson(["total" => 2])
            ->assertJsonStructure(
                $this->buildCollectionStructure(
                    static::$getTrailerResponseStructure
                )
            );
    }

    protected function buildLoan($upTo = null)
    {
        $community = factory(Community::class)->create();
        $pricing = factory(Pricing::class)->create([
            "community_id" => $community->id,
            "object_type" => "App\Models\Trailer",
        ]);

        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $user = factory(User::class)->create();
        $owner = factory(Owner::class)->create(["user_id" => $user]);

        $loanable = factory(Trailer::class)->create([
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
