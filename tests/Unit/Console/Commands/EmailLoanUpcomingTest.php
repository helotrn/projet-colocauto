<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\EmailLoanUpcoming as EmailLoanUpcomingCommand;
use App\Models\Bike;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Log;

use Tests\TestCase;

class EmailLoanUpcomingTest extends TestCase
{
    public function testUpcomingLoanNotSelfService()
    {
        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create([]);

        $borrowerUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([]);

        $bike = factory(Bike::class)->create([
            "owner_id" => $ownerUser->owner->id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => false,
        ]);

        $loan = factory(Loan::class)
            ->states("withInProcessTakeover")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $bike->community_id,
                // Loan created more than 3 hours ago.
                "created_at" => Carbon::now()->subtract(185, "minutes"),
                // Loan starting in less than 3 hours, but later than now.
                "departure_at" => Carbon::now()->add(175, "minutes"),
                "duration_in_minutes" => 600,
            ]);

        Log::spy();

        $this->artisan("email:loan:upcoming")->assertExitCode(0);

        // Example of the expected calls to Log::info()
        //   Fetching loans starting in three hours or less created at least three hours before now...
        //   Sending LoanUpcoming email to borrower at: frederic.richard@example.org
        //   OK Sending App\Mail\Loan\Upcoming to frederic.richard@example.org
        //   Sending LoanUpcoming email to owner at: camille.vachon@example.org
        //   OK Sending App\Mail\Loan\Upcoming to camille.vachon@example.org
        //   Done.
        Log::shouldHaveReceived("info")->times(6);

        // Reload from database.
        $loan->refresh();
        // Check that the email is marked as sent.
        $this->assertEquals(["sent_loan_upcoming_email" => true], $loan->meta);
    }

    public function testUpcomingLoanSelfService()
    {
        $this->markTestSkipped('Self Service management changed, update the test');

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create([]);

        $borrowerUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([]);

        $bike = factory(Bike::class)->create([
            "owner_id" => $ownerUser->owner->id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => true,
        ]);

        $loan = factory(Loan::class)
            ->states("withInProcessTakeover")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $bike->community_id,
                // Loan created more than 3 hours ago.
                "created_at" => Carbon::now()->subtract(185, "minutes"),
                // Loan starting in less than 3 hours, but later than now.
                "departure_at" => Carbon::now()->add(175, "minutes"),
                "duration_in_minutes" => 600,
            ]);

        Log::spy();

        $this->artisan("email:loan:upcoming")->assertExitCode(0);

        // Example of the expected calls to Log::info()
        // Mail to owner is not expected.
        //   Fetching loans starting in three hours or less created at least three hours before now...
        //   Sending LoanUpcoming email to borrower at: frederic.richard@example.org
        //   OK Sending App\Mail\Loan\Upcoming to frederic.richard@example.org
        //   Done.
        Log::shouldHaveReceived("info")->times(4);

        // Reload from database.
        $loan->refresh();
        // Check that the email is marked as sent.
        $this->assertEquals(["sent_loan_upcoming_email" => true], $loan->meta);
    }

    public function testLoanNotYetUpcomingSelfService()
    {
        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create([]);

        $borrowerUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([]);

        $bike = factory(Bike::class)->create([
            "owner_id" => $ownerUser->owner->id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => true,
        ]);

        $loan = factory(Loan::class)
            ->states("withInProcessTakeover")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $bike->community_id,
                // Loan created more than 3 hours ago.
                "created_at" => Carbon::now()->subtract(185, "minutes"),
                // Loan starting in more than 3 hours.
                "departure_at" => Carbon::now()->add(185, "minutes"),
                "duration_in_minutes" => 600,
            ]);

        $this->artisan("email:loan:upcoming")->assertExitCode(0);

        // Reload from database.
        $loan->refresh();
        // Check that the email is not marked as sent.
        $this->assertTrue(
            !array_key_exists("sent_loan_upcoming_email", $loan->meta) ||
                false == $loan->meta["sent_loan_upcoming_email"]
        );
    }

    public function testLoanAlreadyDepartedSelfService()
    {
        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create([]);

        $borrowerUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([]);

        $bike = factory(Bike::class)->create([
            "owner_id" => $ownerUser->owner->id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => true,
        ]);

        $loan = factory(Loan::class)
            ->states("withInProcessTakeover")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $bike->community_id,
                // Loan created more than 3 hours ago.
                "created_at" => Carbon::now()->subtract(185, "minutes"),
                // Loan departed already.
                "departure_at" => Carbon::now()->subtract(5, "minutes"),
                "duration_in_minutes" => 600,
            ]);

        $this->artisan("email:loan:upcoming")->assertExitCode(0);

        // Reload from database.
        $loan->refresh();
        // Check that the email is not marked as sent.
        $this->assertTrue(
            !array_key_exists("sent_loan_upcoming_email", $loan->meta) ||
                false == $loan->meta["sent_loan_upcoming_email"]
        );
    }

    public function testLoanCreatedLessThanThreeHoursAgoSelfService()
    {
        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create([]);

        $borrowerUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([]);

        $bike = factory(Bike::class)->create([
            "owner_id" => $ownerUser->owner->id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => true,
        ]);

        $loan = factory(Loan::class)
            ->states("withInProcessTakeover")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $bike->community_id,
                // Loan created less than 3 hours ago.
                "created_at" => Carbon::now()->subtract(175, "minutes"),
                // Loan starting in less than 3 hours, but later than now.
                "departure_at" => Carbon::now()->add(175, "minutes"),
                "duration_in_minutes" => 600,
            ]);

        $this->artisan("email:loan:upcoming")->assertExitCode(0);

        // Reload from database.
        $loan->refresh();
        // Check that the email is not marked as sent.
        $this->assertTrue(
            !array_key_exists("sent_loan_upcoming_email", $loan->meta) ||
                false == $loan->meta["sent_loan_upcoming_email"]
        );
    }

    public function testUpcomingLoanNotSelfServiceWithPretendOption()
    {
        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create([]);

        $borrowerUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([]);

        $bike = factory(Bike::class)->create([
            "owner_id" => $ownerUser->owner->id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => false,
        ]);

        $loan = factory(Loan::class)
            ->states("withInProcessTakeover")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $bike->community_id,
                // Loan created more than 3 hours ago.
                "created_at" => Carbon::now()->subtract(185, "minutes"),
                // Loan starting in less than 3 hours, but later than now.
                "departure_at" => Carbon::now()->add(175, "minutes"),
                "duration_in_minutes" => 600,
            ]);

        Log::spy();

        $this->artisan("email:loan:upcoming", [
            "--pretend" => true,
        ])->assertExitCode(0);

        // Example of the expected calls to Log::info()
        //   Fetching loans starting in three hours or less created at least three hours before now...
        //   Would have sent LoanUpcoming email to borrower at: frederic.richard@example.org for loan with id: 1
        //   Would have sent LoanUpcoming email to owner at: frederic.richard@example.org for loan with id: 1
        //   Done.
        Log::shouldHaveReceived("info")->times(4);

        // Reload from database.
        $loan->refresh();
        // Check that the email is not marked as sent.
        $this->assertTrue(
            !array_key_exists("sent_loan_upcoming_email", $loan->meta) ||
                false == $loan->meta["sent_loan_upcoming_email"]
        );
    }
}
