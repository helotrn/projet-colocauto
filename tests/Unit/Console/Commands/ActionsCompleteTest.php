<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\ActionsComplete as ActionsCompleteCommand;
use App\Models\Action;
use App\Models\Car;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;

use Tests\TestCase;

class ActionsCompleteTest extends TestCase
{
    public function testPaymentActionsCompleted()
    {
        $twoDaysAgo = Carbon::now()
            ->sub(48, "hours")
            ->sub(10, "seconds");
        $oneDayAgo = Carbon::now()
            ->sub(24, "hours")
            ->sub(10, "seconds");

        Carbon::setTestNow($twoDaysAgo);

        $user = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([
                "balance" => 15,
            ]);
        $otherUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create([
                "balance" => 5,
            ]);

        $car = factory(Car::class)->create([
            "community_id" => $user->communities[0]->id,
        ]);

        // First User
        // A completed loan, but not paid, 24 hours ago
        // ==> Should not be changed
        Carbon::setTestNow($oneDayAgo);
        $completedLoan24HoursAgo = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "borrower_id" => $user->borrower->id,
                "community_id" => $car->community_id,
                "departure_at" => Carbon::now()->sub(24, "hours"),
                "loanable_id" => $car->id,
                "platform_tip" => 0,
            ]);

        // A completed loan, not paid, 48 hours ago
        // ==> Should be automatically completed
        Carbon::setTestNow($twoDaysAgo);
        $unpaidCompletedLoan48HoursAgo = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "borrower_id" => $user->borrower->id,
                "community_id" => $car->community_id,
                "departure_at" => $twoDaysAgo,
                "loanable_id" => $car->id,
                "platform_tip" => 0,
            ]);

        // Another completed loan, not paid, 48 hours ago
        // ==> Should not be automatically completed because there is not enough funds
        $unpaidCompletedLoan48HoursAgoNotEnoughFunds = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "borrower_id" => $user->borrower->id,
                "community_id" => $car->community_id,
                "departure_at" => Carbon::now()->sub(48, "hours"),
                "loanable_id" => $car->id,
                "platform_tip" => 0,
            ]);

        // Other User
        // A completed loan, not paid, 48 hours ago
        // ==> Should not be automatically completed because there is not enough funds
        $loanOtherUser = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "borrower_id" => $otherUser->borrower->id,
                "community_id" => $car->community_id,
                "departure_at" => Carbon::now()->sub(48, "hours"),
                "loanable_id" => $car->id,
                "platform_tip" => 0,
            ]);

        // Validate initial state
        Carbon::setTestNow();
        $this->assertEquals("in_process", $completedLoan24HoursAgo->loanStatus);
        $this->assertEquals(
            "in_process",
            $unpaidCompletedLoan48HoursAgo->loanStatus
        );
        $this->assertEquals(
            "in_process",
            $unpaidCompletedLoan48HoursAgoNotEnoughFunds->loanStatus
        );
        $this->assertEquals("in_process", $loanOtherUser->loanStatus);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();
        $unpaidCompletedLoan48HoursAgo = $unpaidCompletedLoan48HoursAgo->fresh();

        // Don't autocomplete less than 48 hours after
        $this->assertEquals("in_process", $completedLoan24HoursAgo->loanStatus);

        // Autocomplete loan when balance is sufficient to pay
        $this->assertEquals(
            "completed",
            $unpaidCompletedLoan48HoursAgo->loanStatus
        );

        // Do not autocomplete loan when balance is not sufficient to pay
        // taking into account the latest changes in balance
        $this->assertEquals(
            "in_process",
            $unpaidCompletedLoan48HoursAgoNotEnoughFunds->loanStatus
        );
        $this->assertEquals("in_process", $loanOtherUser->loanStatus);
    }

    public function testTakeoverActionsCanceled()
    {
        $twoDaysAgo = Carbon::now()
            ->sub(48, "hours")
            ->sub(10, "seconds");
        $twoDaysFromNow = Carbon::now()
            ->add(48, "hours")
            ->add(10, "seconds");

        Carbon::setTestNow();

        $user = factory(User::class)
            ->states("withBorrower", "withCommunity")
            ->create([
                "balance" => 15,
            ]);

        $car = factory(Car::class)->create([
            "community_id" => $user->communities[0]->id,
        ]);

        $intentionLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessTakeover")
            ->create([
                "borrower_id" => $user->borrower->id,
                "community_id" => $car->community_id,
                // Return scheduled 1 hour ago, then cancelled in 48 hours.
                "departure_at" => Carbon::now()->sub(2, "hours"),
                "duration_in_minutes" => 60,
                "loanable_id" => $car->id,
                "platform_tip" => 0,
            ]);

        $intentionLoanInFuture = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessTakeover")
            ->create([
                "borrower_id" => $user->borrower->id,
                "community_id" => $car->community_id,
                // Return scheduled in 1 hour, then don't cancel
                "departure_at" => Carbon::now(),
                "duration_in_minutes" => 60,
                "loanable_id" => $car->id,
                "platform_tip" => 0,
            ]);

        // Validate initial state
        $this->assertEquals("in_process", $intentionLoan->loanStatus);
        $this->assertEquals("in_process", $intentionLoanInFuture->loanStatus);

        // Run the test as if we were two days later.
        Carbon::setTestNow($twoDaysFromNow);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();
        $intentionLoan = $intentionLoan->fresh();
        $intentionLoanInFuture = $intentionLoanInFuture->fresh();

        // This one ended more than 48 hours ago.
        $this->assertEquals("canceled", $intentionLoan->loanStatus);

        // This one ended less than 48 hours ago, and is not to be canceled.
        $this->assertEquals("in_process", $intentionLoanInFuture->loanStatus);
    }
}
