<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\NokeSyncLoans as NokeSyncLoansCommand;
use App\Models\Bike;
use App\Models\Loan;
use Carbon\Carbon;

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

        $now = Carbon::now();
        Carbon::setTestNow($now);

        // Loan that should be asserted in count
        $validLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withCompletedPrePayment")
            ->create([
                "departure_at" => $now->copy()->add(10, "minutes"),
                "loanable_id" => $bikeWithPadlock,
            ]);

        $validFutureLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withCompletedPrePayment")
            ->create([
                "departure_at" => $now->copy()->add(20, "minutes"),
                "loanable_id" => $bikeWithPadlock,
            ]);

        $canceledLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withCompletedPrePayment")
            ->create([
                "canceled_at" => $now->copy()->add(10, "minutes"),
                "departure_at" => $now->copy()->add(10, "minutes"),
                "loanable_id" => $bikeWithPadlock,
            ]);

        $unpaidValidLoan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "departure_at" => $now->copy()->add(10, "minutes"),
                "loanable_id" => $bikeWithPadlock,
            ]);

        // Loan that should be asserted in count
        $paidValidLoan = factory(Loan::class)
            ->states("withAllStepsCompleted", "butPaymentInProcess")
            ->create([
                "departure_at" => $now->copy()->add(10, "minutes"),
                "loanable_id" => $bikeWithPadlock,
            ]);

        $query = NokeSyncLoansCommand::getLoansFromPadlockMacQuery([
            "mac_address" => $bikeWithPadlock->padlock->mac_address,
        ]);

        $this->assertEquals(2, $query->count());
    }
}
