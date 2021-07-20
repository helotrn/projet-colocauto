<?php

namespace Tests\Unit\Models;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Extension;
use App\Models\Loan;
use App\Models\Owner;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExtensionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $owner = factory(Owner::class)->create(["user_id" => $this->user->id]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $this->user->id,
        ]);
        $this->loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);
        $this->loan = factory(Loan::class)
            ->states("withCompletedIntention")
            ->create([
                "loanable_id" => $this->loanable->id,
                "borrower_id" => $borrower->id,
                "duration_in_minutes" => 20,
            ]);
    }

    public function testExtensionMakeLoanableUnavailable()
    {
        $departure = $this->loan->departure_at;
        $anHourLater = (new \Carbon\Carbon($this->loan->departure_at))->add(
            1,
            "hour"
        );
        $threeHoursLater = (new \Carbon\Carbon($this->loan->departure_at))->add(
            3,
            "hour"
        );

        $this->assertFalse($this->loanable->isAvailable($departure, 10));
        $this->assertTrue(
            $this->loanable->isAvailable($departure, 10, [$this->loan->id])
        );

        $this->assertTrue($this->loanable->isAvailable($anHourLater, 10));

        $extension = new Extension();
        $extension->fill([
            "new_duration" => 170,
            "comments_on_extension" => $this->faker->paragraph,
            "type" => "extension",
            "status" => "completed",
        ]);
        $extension->loan_id = $this->loan->id;
        $extension->save();

        $this->assertFalse($this->loanable->isAvailable($anHourLater, 10));
        $this->assertTrue($this->loanable->isAvailable($threeHoursLater, 10));
    }
}
