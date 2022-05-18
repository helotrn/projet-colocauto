<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use App\Models\PrePayment;
use Carbon\Carbon;
use Tests\TestCase;

class PrePaymentTest extends TestCase
{
    public function testComplete_Now()
    {
        $loan = factory(Loan::class)->create();
        $prePayment = factory(PrePayment::class)->make([
            "status" => "in_process",
        ]);
        $loan->prePayment()->save($prePayment);

        $this->assertFalse($prePayment->isCompleted());

        $prePayment->complete();

        $this->assertTrue($prePayment->isCompleted());

        $this->assertNotNull($prePayment->executed_at);
        $this->assertEquals("completed", $prePayment->status);
    }

    public function testComplete_At()
    {
        $loan = factory(Loan::class)->create();
        $prePayment = factory(PrePayment::class)->make([
            "status" => "in_process",
        ]);
        $loan->prePayment()->save($prePayment);

        $this->assertFalse($prePayment->isCompleted());

        $prePayment->complete(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($prePayment->isCompleted());

        $this->assertNotNull($prePayment->executed_at);
        $this->assertEquals("completed", $prePayment->status);
        $this->assertEquals("2022-04-16 12:34:56", $prePayment->executed_at);
    }

    public function testIsCompleted()
    {
        $loan = factory(Loan::class)->create();
        $prePayment = factory(PrePayment::class)->make([
            "status" => "completed",
        ]);
        $loan->prePayment()->save($prePayment);

        $this->assertTrue($prePayment->isCompleted());
    }

    public function testCancel_Now()
    {
        $loan = factory(Loan::class)->create();
        $prePayment = factory(PrePayment::class)->make([
            "status" => "in_process",
        ]);
        $loan->prePayment()->save($prePayment);

        $this->assertFalse($prePayment->isCanceled());

        $prePayment->cancel();

        $this->assertTrue($prePayment->isCanceled());

        $this->assertNotNull($prePayment->executed_at);
        $this->assertEquals("canceled", $prePayment->status);
    }

    public function testCancel_At()
    {
        $loan = factory(Loan::class)->create();
        $prePayment = factory(PrePayment::class)->make([
            "status" => "in_process",
        ]);
        $loan->prePayment()->save($prePayment);

        $this->assertFalse($prePayment->isCanceled());

        $prePayment->cancel(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($prePayment->isCanceled());

        $this->assertNotNull($prePayment->executed_at);
        $this->assertEquals("canceled", $prePayment->status);
        $this->assertEquals("2022-04-16 12:34:56", $prePayment->executed_at);
    }

    public function testIsCanceled()
    {
        $loan = factory(Loan::class)->create();
        $prePayment = factory(PrePayment::class)->make([
            "status" => "canceled",
        ]);
        $loan->prePayment()->save($prePayment);

        $this->assertTrue($prePayment->isCanceled());
    }
}
