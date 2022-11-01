<?php

namespace Tests\Integration;

use App\Events\BorrowerApprovedEvent;
use App\Listeners\SendBorrowerApprovedEmails;
use App\Models\Borrower;
use App\Models\User;
use App\Mail\Borrower\Approved as BorrowerApproved;
use App\Mail\Borrower\Pending as BorrowerPending;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BorrowerTest extends TestCase
{
    private static $getBorrowerResponseStructure = [
        "id",
        "drivers_license_number",
        "has_not_been_sued_last_ten_years",
        "noke_id",
        "submitted_at",
        "approved_at",
    ];

    public function testCreateBorrowers()
    {
        $data = [
            "drivers_license_number" => $this->faker->numberBetween(
                $min = 1111111111,
                $max = 999999999
            ),
            "has_not_been_sued_last_ten_years" => $this->faker->boolean,
            "noke_id" => $this->faker->numberBetween(
                $min = 000000000,
                $max = 999999999
            ),
            "submitted_at" => $this->faker->date(
                $format = "Y-m-d",
                $max = "now"
            ),
            "approved_at" => null,
            "user_id" => $this->user->id,
        ];

        $response = $this->json("POST", "/api/v1/borrowers", $data);

        $response->assertStatus(405);
    }

    public function testShowBorrowers()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);

        $response = $this->json("GET", "/api/v1/borrowers/$borrower->id");

        $response
            ->assertStatus(200)
            ->assertJson(["id" => $borrower->id])
            ->assertJsonStructure(static::$getBorrowerResponseStructure);
    }

    public function testUpdateBorrowers()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $data = [
            "drivers_license_number" => $this->faker->numberBetween(
                $min = 1111111111,
                $max = 999999999
            ),
        ];

        $response = $this->json(
            "PUT",
            "/api/v1/borrowers/$borrower->id",
            $data
        );

        $response->assertStatus(405);
    }

    public function testDeleteBorrowers()
    {
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);

        $response = $this->json("DELETE", "/api/v1/borrowers/$borrower->id");
        $response->assertStatus(405);
    }

    public function testListBorrowers()
    {
        $borrowers = factory(Borrower::class, 2)
            ->create(["user_id" => $this->user->id])
            ->map(function ($borrower) {
                return $borrower->only(static::$getBorrowerResponseStructure);
            });

        $response = $this->json("GET", "/api/v1/borrowers");

        $response
            ->assertStatus(200)
            ->assertJson(["total" => 2])
            ->assertJsonStructure(
                $this->buildCollectionStructure(
                    static::$getBorrowerResponseStructure
                )
            );
    }

    public function testApproveBorrowers()
    {
        $meta = [];
        $meta["sent_registration_approved_email"] = true;

        // Fake user with registration approved
        $user = factory(User::class)->create([
            "meta" => $meta,
        ]);

        $borrower = factory(Borrower::class)->create([
            "user_id" => $user->id,
        ]);

        $response = $this->json("GET", "/api/v1/borrowers/$borrower->id");
        $response->assertStatus(200)->assertJson(["approved_at" => null]);

        $approvedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($approvedAtDate);

        $response = $this->json(
            "PUT",
            "/api/v1/users/{$borrower->user->id}/borrower/approve"
        );
        $response->assertStatus(200);

        $response = $this->json("GET", "/api/v1/borrowers/$borrower->id");
        $response
            ->assertStatus(200)
            ->assertJson(["approved_at" => $approvedAtDate]);

        $event = new BorrowerApprovedEvent($user);

        Mail::fake();

        // Don't trigger event. Only test listener.
        $listener = app()->make(SendBorrowerApprovedEmails::class);
        $listener->handle($event);

        // Mail to borrower.
        Mail::assertQueued(BorrowerApproved::class, function ($mail) use (
            $user
        ) {
            return $mail->hasTo($user->email);
        });
    }

    public function testPendingBorrowers()
    {
        // Fake user without registration approved
        $user = $this->user;
        $borrower = factory(Borrower::class)->create([
            "user_id" => $user->id,
        ]);

        $response = $this->json("GET", "/api/v1/borrowers/$borrower->id");
        $response->assertStatus(200)->assertJson(["approved_at" => null]);

        $approvedAtDate = Carbon::now()->format("Y-m-d h:m:s");
        Carbon::setTestNow($approvedAtDate);

        $response = $this->json(
            "PUT",
            "/api/v1/users/{$borrower->user->id}/borrower/approve"
        );
        $response->assertStatus(200);

        $response = $this->json("GET", "/api/v1/borrowers/$borrower->id");
        $response
            ->assertStatus(200)
            ->assertJson(["approved_at" => $approvedAtDate]);

        $event = new BorrowerApprovedEvent($user);

        Mail::fake();

        // Don't trigger event. Only test listener.
        $listener = app()->make(SendBorrowerApprovedEmails::class);
        $listener->handle($event);

        // Mail to borrower.
        Mail::assertQueued(BorrowerPending::class, function ($mail) use (
            $user
        ) {
            return $mail->hasTo($user->email);
        });
    }
}
