<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use App\Models\Payment;
use Carbon\Carbon;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function testComplete_Now()
    {
        $loan = factory(Loan::class)->create();
        $payment = factory(Payment::class)->make(["status" => "in_process"]);
        $loan->payment()->save($payment);

        $this->assertFalse($payment->isCompleted());

        $payment->complete();

        $this->assertTrue($payment->isCompleted());

        $this->assertNotNull($payment->executed_at);
        $this->assertEquals("completed", $payment->status);
    }

    public function testComplete_At()
    {
        $loan = factory(Loan::class)->create();
        $payment = factory(Payment::class)->make(["status" => "in_process"]);
        $loan->payment()->save($payment);

        $this->assertFalse($payment->isCompleted());

        $payment->complete(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($payment->isCompleted());

        $this->assertNotNull($payment->executed_at);
        $this->assertEquals("completed", $payment->status);
        $this->assertEquals("2022-04-16 12:34:56", $payment->executed_at);
    }

    public function testIsCompleted()
    {
        $loan = factory(Loan::class)->create();
        $payment = factory(Payment::class)->make(["status" => "completed"]);
        $loan->payment()->save($payment);

        $this->assertTrue($payment->isCompleted());
    }
}
