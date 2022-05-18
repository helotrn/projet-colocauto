<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use App\Models\Handover;
use Carbon\Carbon;
use Tests\TestCase;

class HandoverTest extends TestCase
{
    public function testComplete_Now()
    {
        $loan = factory(Loan::class)->create();
        $handover = factory(Handover::class)->make(["status" => "in_process"]);
        $loan->handover()->save($handover);

        $this->assertFalse($handover->isCompleted());

        $handover->complete();

        $this->assertTrue($handover->isCompleted());

        $this->assertNotNull($handover->executed_at);
        $this->assertEquals("completed", $handover->status);
    }

    public function testComplete_At()
    {
        $loan = factory(Loan::class)->create();
        $handover = factory(Handover::class)->make(["status" => "in_process"]);
        $loan->handover()->save($handover);

        $this->assertFalse($handover->isCompleted());

        $handover->complete(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($handover->isCompleted());

        $this->assertNotNull($handover->executed_at);
        $this->assertEquals("completed", $handover->status);
        $this->assertEquals("2022-04-16 12:34:56", $handover->executed_at);
    }

    public function testIsCompleted()
    {
        $loan = factory(Loan::class)->create();
        $handover = factory(Handover::class)->make(["status" => "completed"]);
        $loan->handover()->save($handover);

        $this->assertTrue($handover->isCompleted());
    }

    public function testContest_Now()
    {
        $loan = factory(Loan::class)->create();
        $handover = factory(Handover::class)->make(["status" => "in_process"]);
        $loan->handover()->save($handover);

        $this->assertFalse($handover->isContested());

        $handover->contest();

        $this->assertTrue($handover->isContested());

        $this->assertNotNull($handover->executed_at);
        // Status = canceled means contested.
        $this->assertEquals("canceled", $handover->status);
    }

    public function testContest_At()
    {
        $loan = factory(Loan::class)->create();
        $handover = factory(Handover::class)->make(["status" => "in_process"]);
        $loan->handover()->save($handover);

        $this->assertFalse($handover->isContested());

        $handover->contest(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($handover->isContested());

        $this->assertNotNull($handover->executed_at);
        // Status = canceled means contested.
        $this->assertEquals("canceled", $handover->status);
        $this->assertEquals("2022-04-16 12:34:56", $handover->executed_at);
    }

    public function testIsContested()
    {
        $loan = factory(Loan::class)->create();
        // Status = canceled means contested.
        $handover = factory(Handover::class)->make(["status" => "canceled"]);
        $loan->handover()->save($handover);

        $this->assertTrue($handover->isContested());
    }
}
