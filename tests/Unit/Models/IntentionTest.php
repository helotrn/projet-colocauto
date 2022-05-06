<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use App\Models\Intention;
use Carbon\Carbon;
use Tests\TestCase;

class IntentionTest extends TestCase
{
    public function testComplete_Now()
    {
        $loan = factory(Loan::class)->create();
        $intention = factory(Intention::class)->make([
            "status" => "in_process",
        ]);
        $loan->intention()->save($intention);

        $this->assertFalse($intention->isCompleted());

        $intention->complete();

        $this->assertTrue($intention->isCompleted());

        $this->assertNotNull($intention->executed_at);
        $this->assertEquals("completed", $intention->status);
    }

    public function testComplete_At()
    {
        $loan = factory(Loan::class)->create();
        $intention = factory(Intention::class)->make([
            "status" => "in_process",
        ]);
        $loan->intention()->save($intention);

        $this->assertFalse($intention->isCompleted());

        $intention->complete(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($intention->isCompleted());

        $this->assertNotNull($intention->executed_at);
        $this->assertEquals("completed", $intention->status);
        $this->assertEquals("2022-04-16 12:34:56", $intention->executed_at);
    }

    public function testIsCompleted()
    {
        $loan = factory(Loan::class)->create();
        $intention = factory(Intention::class)->make([
            "status" => "completed",
        ]);
        $loan->intention()->save($intention);

        $this->assertTrue($intention->isCompleted());
    }

    public function testCancel_Now()
    {
        $loan = factory(Loan::class)->create();
        $intention = factory(Intention::class)->make([
            "status" => "in_process",
        ]);
        $loan->intention()->save($intention);

        $this->assertFalse($intention->isCanceled());

        $intention->cancel();

        $this->assertTrue($intention->isCanceled());

        $this->assertNotNull($intention->executed_at);
        $this->assertEquals("canceled", $intention->status);
    }

    public function testCancel_At()
    {
        $loan = factory(Loan::class)->create();
        $intention = factory(Intention::class)->make([
            "status" => "in_process",
        ]);
        $loan->intention()->save($intention);

        $this->assertFalse($intention->isCanceled());

        $intention->cancel(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($intention->isCanceled());

        $this->assertNotNull($intention->executed_at);
        $this->assertEquals("canceled", $intention->status);
        $this->assertEquals("2022-04-16 12:34:56", $intention->executed_at);
    }

    public function testIsCanceled()
    {
        $loan = factory(Loan::class)->create();
        $intention = factory(Intention::class)->make([
            "status" => "canceled",
        ]);
        $loan->intention()->save($intention);

        $this->assertTrue($intention->isCanceled());
    }
}
