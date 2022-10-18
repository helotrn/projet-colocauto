<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use Carbon\CarbonImmutable;
use Tests\TestCase;

use function PHPUnit\Framework\assertTrue;

class ExtensionTest extends TestCase
{
    protected $loan;
    protected $loanable;
    protected $departure;

    public function setUp(): void
    {
        parent::setUp();

        $this->user->communities()->save(factory(Community::class)->make());
        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $this->loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);
        $this->departure = CarbonImmutable::now();
        $this->loan = factory(Loan::class)->create([
            "loanable_id" => $this->loanable->id,
            "borrower_id" => $borrower->id,
            "duration_in_minutes" => 15,
            "departure_at" => $this->departure,
        ]);
    }

    public function testCreateExtensions()
    {
        $data = [
            "new_duration" => 30,
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "in_process",
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans/{$this->loan->id}/actions",
            $data
        );

        $response->assertStatus(201)->assertJson($data);
    }

    public function testCreateExtension_failsIfTooShort()
    {
        $data = [
            "new_duration" => 10,
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "in_process",
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans/{$this->loan->id}/actions",
            $data
        );

        $response->assertStatus(422)->assertJson([
            "errors" => [
                "new_duration" => [],
            ],
        ]);

        $data = [
            "new_duration" => 25, // Only 10 minutes in the future
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "in_process",
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans/{$this->loan->id}/actions",
            $data
        );

        $response->assertStatus(422)->assertJson([
            "errors" => [
                "new_duration" => [],
            ],
        ]);
    }

    public function testCreateSecondExtension_failsIfEarlier()
    {
        $data = [
            "new_duration" => 60,
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "completed",
        ];

        $this->json("POST", "/api/v1/loans/{$this->loan->id}/actions", $data);

        $data = [
            "new_duration" => 40, // Before first extension
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "in_process",
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans/{$this->loan->id}/actions",
            $data
        );

        $response->assertStatus(422)->assertJson([
            "errors" => [
                "new_duration" => [],
            ],
        ]);
    }

    public function testCreateExtensionsForSelfServiceLoanable()
    {
        $this->loanable->is_self_service = true;
        $this->loanable->save();

        $data = [
            "new_duration" => 30,
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "in_process",
        ];

        $response = $this->json(
            "POST",
            "/api/v1/loans/{$this->loan->id}/actions",
            $data
        );

        $response->assertStatus(201)->assertJson(
            array_merge($data, [
                "status" => "completed",
            ])
        );

        $this->loan->refresh();
        assertTrue(
            $this->departure
                ->copy()
                ->addMinutes(30)
                ->equalTo($this->loan->actual_return_at)
        );
    }
}
