<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\NokeSyncLoans as NokeSyncLoansCommand;
use App\Models\Bike;
use App\Models\Loan;
use Carbon\CarbonImmutable;

use Tests\TestCase;

class NokeSyncLoansTest extends TestCase
{
    public function testGetLoansFromPadlockMacQuery()
    {
        $query = NokeSyncLoansCommand::getLoansFromPadlockMacQuery([
            "mac_address" => "0D:34:F2:3E:0F:2F",
        ]);

        $query->get();

        // Assert that we ended up here.
        $this->assertTrue(true);
    }

    public function testGetLoansFromPadlockMacTakesCancelationIntoAccount()
    {
        $bikeWithPadlock = factory(Bike::class)
            ->states("withCommunity", "withPadlock")
            ->create();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        // Loan starts in more than 15 minutes, should not have access.
        $loanStartingLater = factory(Loan::class)
            ->states("withCompletedIntention", "withCompletedPrePayment")
            ->create([
                "departure_at" => $now->addMinutes(20),
                "duration_in_minutes" => 60,
                "loanable_id" => $bikeWithPadlock,
            ])
            ->refresh();

        // Loan starts in less than 15 minutes, should have access.
        $loanStartingSoon = factory(Loan::class)
            ->states("withCompletedIntention", "withCompletedPrePayment")
            ->create([
                "departure_at" => $now->addMinutes(10),
                "duration_in_minutes" => 60,
                "loanable_id" => $bikeWithPadlock,
            ])
            ->refresh();

        // Loan in process.
        $loanInProcess = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "departure_at" => $now->subMinutes(20),
                "duration_in_minutes" => 60,
                "loanable_id" => $bikeWithPadlock,
            ]);

        // Loan ended less than 15 minutes ago, should have access.
        $loanEndedRecently = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "departure_at" => $now->subMinutes(70),
                "duration_in_minutes" => 60,
                "loanable_id" => $bikeWithPadlock,
            ]);

        // This one should remain accessible
        $loanEndedEarlier = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "departure_at" => $now->subMinutes(80),
                "duration_in_minutes" => 60,
                "loanable_id" => $bikeWithPadlock,
            ]);

        // Cancelled loans should never grant access.
        $canceledLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withCompletedPrePayment")
            ->create([
                "canceled_at" => $now->addMinutes(10),
                "departure_at" => $now->addMinutes(10),
                "duration_in_minutes" => 60,
                "loanable_id" => $bikeWithPadlock,
            ])
            ->refresh();

        $query = NokeSyncLoansCommand::getLoansFromPadlockMacQuery([
            "mac_address" => $bikeWithPadlock->padlock->mac_address,
        ]);

        $loans = $query->get();

        $testLoanIds = [];
        foreach ($loans as $loan) {
            $testLoanIds[] = $loan->id;
        }

        $this->assertEqualsCanonicalizing(
            [$loanStartingSoon->id, $loanInProcess->id, $loanEndedRecently->id],
            $testLoanIds
        );
    }

    // Mettre dans l<autre paquet, ou tout s/parer,
    // TestGetLockUsers
    public function testGetLockUsers_PadlockAccessibleDelay()
    {
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $bikeWithPadlock = factory(Bike::class)
            ->states("withCommunity", "withPadlock")
            ->create();

        // This one should not be accessible anymore
        $loanEndingBeforeDelay = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "departure_at" => $now->subMinutes(120),
                "duration_in_minutes" => 100,
                "loanable_id" => $bikeWithPadlock,
            ]);

        // This one should remain accessible
        $loanEndingAfterDelay = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "departure_at" => $now->subMinutes(120),
                "duration_in_minutes" => 110,
                "loanable_id" => $bikeWithPadlock,
            ]);

        $query = NokeSyncLoansCommand::getLoansFromPadlockMacQuery([
            "mac_address" => $bikeWithPadlock->padlock->mac_address,
        ]);

        $loans = $query->get();

        $testLoanIds = [];
        foreach ($loans as $loan) {
            $testLoanIds[] = $loan->id;
        }

        $this->assertEquals([$loanEndingAfterDelay->id], $testLoanIds);
    }
}
