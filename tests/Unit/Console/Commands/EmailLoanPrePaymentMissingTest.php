<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\EmailLoanPrePaymentMissing as EmailLoanPrePaymentMissingCommand;
use App\Models\Loan;
use Carbon\Carbon;
use Log;

use Tests\TestCase;

class EmailLoanPrePaymentMissingTest extends TestCase
{
    public function testLoadPrePaymentMissing()
    {
        $loan = factory(Loan::class)
            ->states("withInProcessPrePayment")
            ->create([
                // Loan created more than 3 hours ago.
                "created_at" => Carbon::now()->subtract(185, "minutes"),
                // Loan starting in less than 24 hours, but later than now.
                "departure_at" => Carbon::now()->add(240, "minutes"),
            ]);

        Log::spy();

        $this->artisan("email:loan:pre_payment_missing")->assertExitCode(0);

        Log::shouldHaveReceived("info")->times(4);

        // Reload from database.
        $loan->refresh();
        // Check that the email is marked as sent.
        $this->assertEquals(
            ["sent_loan_pre_payment_missing_email" => true],
            $loan->meta
        );
    }
}
