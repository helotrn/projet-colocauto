<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use App\Models\Takeover;
use Carbon\Carbon;
use Tests\TestCase;

class TakeoverTest extends TestCase
{
    public function testComplete_Now()
    {
        $loan = factory(Loan::class)->create();
        $takeover = factory(Takeover::class)->make(["status" => "in_process"]);
        $loan->takeover()->save($takeover);

        $this->assertFalse($takeover->isCompleted());

        $takeover->complete();

        $this->assertTrue($takeover->isCompleted());

        $this->assertNotNull($takeover->executed_at);
        $this->assertEquals("completed", $takeover->status);
    }

    public function testComplete_At()
    {
        $loan = factory(Loan::class)->create();
        $takeover = factory(Takeover::class)->make(["status" => "in_process"]);
        $loan->takeover()->save($takeover);

        $this->assertFalse($takeover->isCompleted());

        $takeover->complete(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($takeover->isCompleted());

        $this->assertNotNull($takeover->executed_at);
        $this->assertEquals("completed", $takeover->status);
        $this->assertEquals("2022-04-16 12:34:56", $takeover->executed_at);
    }

    public function testIsCompleted()
    {
        $loan = factory(Loan::class)->create();
        $takeover = factory(Takeover::class)->make(["status" => "completed"]);
        $loan->takeover()->save($takeover);

        $this->assertTrue($takeover->isCompleted());
    }

    public function testContest_Now()
    {
        $loan = factory(Loan::class)->create();
        $takeover = factory(Takeover::class)->make(["status" => "in_process"]);
        $loan->takeover()->save($takeover);

        $this->assertFalse($takeover->isContested());

        $takeover->contest();

        $this->assertTrue($takeover->isContested());

        $this->assertNotNull($takeover->executed_at);
        // Status = canceled means contested.
        $this->assertEquals("canceled", $takeover->status);
    }

    public function testContest_At()
    {
        $loan = factory(Loan::class)->create();
        $takeover = factory(Takeover::class)->make(["status" => "in_process"]);
        $loan->takeover()->save($takeover);

        $this->assertFalse($takeover->isContested());

        $takeover->contest(new Carbon("2022-04-16 12:34:56"));

        $this->assertTrue($takeover->isContested());

        $this->assertNotNull($takeover->executed_at);
        // Status = canceled means contested.
        $this->assertEquals("canceled", $takeover->status);
        $this->assertEquals("2022-04-16 12:34:56", $takeover->executed_at);
    }

    public function testIsContested()
    {
        $loan = factory(Loan::class)->create();
        // Status = canceled means contested.
        $takeover = factory(Takeover::class)->make(["status" => "canceled"]);
        $loan->takeover()->save($takeover);

        $this->assertTrue($takeover->isContested());
    }
}
