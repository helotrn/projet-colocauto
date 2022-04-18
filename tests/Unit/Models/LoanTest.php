<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use Tests\TestCase;
use Carbon\Carbon;

class LoanTest extends TestCase
{
    public function testCancel_Now()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->cancel();

        $this->assertTrue($loan->isCanceled());

        $this->assertNotNull($loan->canceled_at);
        $this->assertEquals("canceled", $loan->status);
    }

    public function testCancel_At()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->cancel(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($loan->isCanceled());

        $this->assertNotNull($loan->canceled_at);
        $this->assertEquals("canceled", $loan->status);
        $this->assertEquals("2022-04-16 12:34:56", $loan->canceled_at);
    }

    public function testIsCanceled_Status()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->status = "canceled";

        $this->assertTrue($loan->isCanceled());
    }

    public function testIsCanceled_CanceledAt()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->canceled_at = new Carbon();

        $this->assertTrue($loan->isCanceled());
    }

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
