<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use Tests\TestCase;

class LoanTest extends TestCase
{
    public function testGetStatusFromActions_IntentionInProcess()
    {
        $loan = factory(Loan::class)
            ->states("withInProcessIntention")
            ->create();

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Assert that loan is in_process.
        $this->assertEquals("in_process", $loan->getStatusFromActions());
    }

    public function testGetStatusFromActions_PaymentInProcess()
    {
        $loan = factory(Loan::class)
            ->states(["withAllStepsCompleted", "butPaymentInProcess"])
            ->create();

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Assert that loan is in_process.
        $this->assertEquals("in_process", $loan->getStatusFromActions());
    }

    public function testGetStatusFromActions_PaymentCompleted()
    {
        $loan = factory(Loan::class)
            ->states(["withAllStepsCompleted"])
            ->create();

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Assert has payment completed.
        $this->assertEquals(
            "completed",
            $loan->payment ? $loan->payment->status : ""
        );

        // Assert that loan is completed.
        $this->assertEquals("completed", $loan->getStatusFromActions());
    }
}
