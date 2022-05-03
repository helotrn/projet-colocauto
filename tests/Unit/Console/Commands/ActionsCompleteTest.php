<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\ActionsComplete as ActionsCompleteCommand;
use App\Models\Action;
use App\Models\Bike;
use App\Models\Car;
use App\Models\Extension;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;

use Tests\TestCase;

class ActionsCompleteTest extends TestCase
{
    public function testGetActiveLoansScheduledToReturnBefore()
    {
        $expectedLoanIds = [];

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower", "withPaidCommunity")
            ->create();

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
        ]);

        $unpaidCompletedLoanEndingMoreThan48HoursAgo = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "community_id" => $loanable->community_id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "loanable_id" => $loanable->id,
                "platform_tip" => 0,
            ]);
        $expectedLoanIds[] = $unpaidCompletedLoanEndingMoreThan48HoursAgo->id;

        $unpaidCompletedLoanEndingLessThan48HoursAgo = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "community_id" => $loanable->community_id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 70,
                "loanable_id" => $loanable->id,
                "platform_tip" => 0,
            ])
            ->fresh();

        $unpaidCompletedLoanEndingLessThan48HoursAgoCanceled = factory(
            Loan::class
        )
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "community_id" => $loanable->community_id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 70,
                "loanable_id" => $loanable->id,
                "platform_tip" => 0,
                "canceled_at" => $twoDaysAgo->subMinutes(30),
            ]);

        $unpaidCompletedLoanEndingMoreThan48HoursAgoExtended = factory(
            Loan::class
        )
            ->states(
                "withAllStepsCompleted",
                "butPaymentInProcess",
                // Extension: new duration = 120 minutes.
                "withCompletedExtension"
            )
            ->create([
                "borrower_id" => $borrowerUser->borrower->id,
                "community_id" => $loanable->community_id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "loanable_id" => $loanable->id,
                "platform_tip" => 0,
            ]);
        $expectedLoanIds[] =
            $unpaidCompletedLoanEndingMoreThan48HoursAgoExtended->id;

        $scheduledLoans = ActionsCompleteCommand::getActiveLoansScheduledToReturnBefore(
            $twoDaysAgo
        );

        $scheduledLoanIds = [];
        foreach ($scheduledLoans as $loan) {
            $scheduledLoanIds[] = $loan->id;
        }

        // Assert equals, order not important.
        $this->assertEqualsCanonicalizing($expectedLoanIds, $scheduledLoanIds);
    }

    public function testIntentionInProcess_LoanNotExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states("withInProcessIntention")
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 70,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->intention ? $loan->intention->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan and intention must remain in process
        $this->assertEquals(
            "in_process",
            $loan->intention ? $loan->intention->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testIntentionInProcess_LoanExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states("withInProcessIntention")
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->intention ? $loan->intention->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Intention remains in process, but loan must be canceled.
        $this->assertEquals(
            "in_process",
            $loan->intention ? $loan->intention->status : ""
        );
        $this->assertEquals("canceled", $loan->status);
    }

    public function testTakeoverInProcess_LoanNotExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessTakeover")
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 70,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan and takeover must remain in process
        $this->assertEquals(
            "in_process",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testTakeoverInProcess_LoanExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessTakeover")
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Takeover remains in process, but loan must be canceled.
        $this->assertEquals(
            "in_process",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals("canceled", $loan->status);
    }

    public function testHandoverInProcess_LoanNotExpired_BalanceSufficient()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withCompletedTakeover",
                "withInProcessHandover"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $loanable->community_id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 70,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan and handover must remain in process
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testHandoverInProcess_LoanExpired_BalanceSufficient()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withCompletedTakeover",
                "withInProcessHandover"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $loanable->community_id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        $loan->handover->executed_at = null;

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan, handover and payment must be completed
        $this->assertEquals(
            "completed",
            $loan->handover ? $loan->handover->status : ""
        );
        // Complete this test after #950
        // $this->assertEquals(
        //     "completed",
        //     $loan->payment ? $loan->payment->status : ""
        // );
        // $this->assertEquals("completed", $loan->status);
    }

    public function testHandoverInProcess_LoanExpired_BalanceNotSufficient()
    {
        $loanableIsSelfService = false;
        $loanCost = 30;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withCompletedTakeover",
                "withInProcessHandover"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $loanable->community_id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Handover must be completed
        // Loan and payment must remain in_process
        $this->assertEquals(
            "completed",
            $loan->handover ? $loan->handover->status : ""
        );
        // Complete this test after #950
        // $this->assertEquals(
        //     "in_process",
        //     $loan->payment ? $loan->payment->status : ""
        // );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testHandoverInProcess_ExtensionAccepted_NotExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withCompletedTakeover",
                "withInProcessHandover"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $loanable->community_id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                // Loan would be expired if it was not for the extension.
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Create extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 70,
                "status" => "completed",
                "executed_at" => CarbonImmutable::now(),
            ])
        );

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        $this->assertCount(1, $loan->extensions);
        foreach ($loan->extensions as $extension) {
            $this->assertEquals("completed", $extension->status);
        }

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan and handover must remain in process
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testHandoverInProcess_ExtensionAccepted_Expired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withCompletedTakeover",
                "withInProcessHandover"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $loanable->community_id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 30,
                "platform_tip" => $loanCost,
            ]);

        // Create extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 50,
                "status" => "completed",
                "executed_at" => CarbonImmutable::now(),
            ])
        );

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        $this->assertCount(1, $loan->extensions);
        foreach ($loan->extensions as $extension) {
            $this->assertEquals("completed", $extension->status);
        }

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan, handover and payment must be completed
        $this->assertEquals(
            "completed",
            $loan->handover ? $loan->handover->status : ""
        );
        // Complete this test after #950
        // $this->assertEquals(
        //     "completed",
        //     $loan->payment ? $loan->payment->status : ""
        // );
        // $this->assertEquals("completed", $loan->status);
    }

    public function testHandoverInProcess_TakeoverContested_LoanExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withContestedTakeover",
                "withInProcessHandover"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "canceled",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Takeover remains canceled. Handover and loan remain in process.
        $this->assertEquals(
            "canceled",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testPaymentInProcess_LoanNotExpired_BalanceSufficient()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 70,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        // Complete this test after #950
        // $this->assertEquals("in_process", $loan->payment ? $loan->payment->status : "");
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan and payment must remain in process
        $this->assertEquals(
            "in_process",
            $loan->payment ? $loan->payment->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testPaymentInProcess_LoanExpired_BalanceSufficient()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        // Complete this test after #950
        // $this->assertEquals("in_process", $loan->payment ? $loan->payment->status : "");
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan and payment must be completed
        $this->assertEquals(
            "completed",
            $loan->payment ? $loan->payment->status : ""
        );
        $this->assertEquals("completed", $loan->status);
    }

    public function testPaymentInProcess_LoanExpired_BalanceNotSufficient()
    {
        $loanableIsSelfService = false;
        $loanCost = 30;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        // Complete this test after #950
        // $this->assertEquals("in_process", $loan->payment ? $loan->payment->status : "");
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Loan and payment must remain in process
        $this->assertEquals(
            "in_process",
            $loan->payment ? $loan->payment->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testExtensionInProcess_LoanExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withCompletedTakeover",
                "withInProcessHandover"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $loanable->community_id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Create extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 70,
                "status" => "in_process",
                "executed_at" => CarbonImmutable::now(),
            ])
        );

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "in_process",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        $this->assertCount(1, $loan->extensions);
        foreach ($loan->extensions as $extension) {
            $this->assertEquals("in_process", $extension->status);
        }

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Extension was not accepted before initial loan expiration and thus
        // must be canceled.
        $this->assertCount(1, $loan->extensions);
        foreach ($loan->extensions as $extension) {
            $this->assertEquals("canceled", $extension->status);
        }

        // Loan, handover and payment must be completed
        $this->assertEquals(
            "completed",
            $loan->handover ? $loan->handover->status : ""
        );
        // Complete this test after #950
        // $this->assertEquals(
        //     "completed",
        //     $loan->payment ? $loan->payment->status : ""
        // );
        // $this->assertEquals("completed", $loan->status);
    }

    public function testPaymentInProcess_TakeoverContested_LoanExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withContestedTakeover",
                "withCompletedHandover",
                "withInProcessPayment"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "canceled",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals(
            "completed",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals(
            "in_process",
            $loan->payment ? $loan->payment->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Things remain intact.
        $this->assertEquals(
            "canceled",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals(
            "completed",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals(
            "in_process",
            $loan->payment ? $loan->payment->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }

    public function testPaymentInProcess_HandoverContested_LoanExpired()
    {
        $loanableIsSelfService = false;
        $loanCost = 0;

        $twoDaysAgo = CarbonImmutable::now()->sub(48, "hours");
        $moreThanTwoDaysAgo = CarbonImmutable::now()->sub(54, "hours");

        $ownerUser = factory(User::class)
            ->states("withOwner", "withPaidCommunity")
            ->create();

        $borrowerUser = factory(User::class)
            ->states("withBorrower")
            ->create([
                "balance" => 15,
            ]);

        $loanable = factory(Car::class)->create([
            "owner_id" => $ownerUser->owner_id,
            "community_id" => $ownerUser->communities[0]->id,
            "is_self_service" => $loanableIsSelfService,
        ]);

        CarbonImmutable::setTestNow($moreThanTwoDaysAgo);

        $loan = factory(Loan::class)
            ->states(
                "withCompletedIntention",
                "withCompletedTakeover",
                "withContestedHandover",
                "withInProcessPayment"
            )
            ->create([
                "loanable_id" => $loanable->id,
                "community_id" => $ownerUser->communities[0]->id,
                "borrower_id" => $borrowerUser->borrower->id,
                "departure_at" => $twoDaysAgo->subMinutes(60),
                "duration_in_minutes" => 50,
                "platform_tip" => $loanCost,
            ]);

        // Setup is finished, set back test time to now.
        CarbonImmutable::setTestNow();

        // Validate preconditions
        $this->assertEquals(
            "completed",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals(
            "canceled",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals(
            "in_process",
            $loan->payment ? $loan->payment->status : ""
        );
        $this->assertEquals("in_process", $loan->status);

        // Run the command
        app(ActionsCompleteCommand::class)->handle();

        // Ensure we fetch loan back from the database
        $loan = $loan->fresh();

        // Things remain intact.
        $this->assertEquals(
            "completed",
            $loan->takeover ? $loan->takeover->status : ""
        );
        $this->assertEquals(
            "canceled",
            $loan->handover ? $loan->handover->status : ""
        );
        $this->assertEquals(
            "in_process",
            $loan->payment ? $loan->payment->status : ""
        );
        $this->assertEquals("in_process", $loan->status);
    }
}
