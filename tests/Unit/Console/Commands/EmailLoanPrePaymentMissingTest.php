<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\EmailLoanPrePaymentMissing as EmailLoanPrePaymentMissingCommand;
use App\Models\Loan;
use Carbon\CarbonImmutable;
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
                "created_at" => CarbonImmutable::now()->subtract(
                    185,
                    "minutes"
                ),
                // Loan starting in less than 24 hours, but later than now.
                "departure_at" => CarbonImmutable::now()->add(240, "minutes"),
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

    public function testLoadPrePaymentMissingWithPretendOption()
    {
        $loan = factory(Loan::class)
            ->states("withInProcessPrePayment")
            ->create([
                // Loan created more than 3 hours ago.
                "created_at" => CarbonImmutable::now()->subtract(
                    185,
                    "minutes"
                ),
                // Loan starting in less than 24 hours, but later than now.
                "departure_at" => CarbonImmutable::now()->add(240, "minutes"),
            ]);

        Log::spy();

        $this->artisan("email:loan:pre_payment_missing", [
            "--pretend" => true,
        ])->assertExitCode(0);

        Log::shouldHaveReceived("info")->times(3);

        // Reload from database.
        $loan->refresh();
        // Check that the email is not marked as sent.
        $this->assertTrue(
            !array_key_exists(
                "sent_loan_pre_payment_missing_email",
                $loan->meta
            ) || $loan->meta["sent_loan_pre_payment_missing_email"] == false
        );
    }
}
