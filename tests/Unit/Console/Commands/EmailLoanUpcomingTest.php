<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\EmailLoanUpcoming as EmailLoanUpcomingCommand;
use App\Models\Loan;
use Carbon\Carbon;

use Tests\TestCase;

class EmailLoanUpcomingTest extends TestCase
{
    public function testGetQuery()
    {
        $createdAt = (new Carbon())->subtract(3, "hours");

        $query = EmailLoanUpcomingCommand::getQuery([
            "created_at" => $createdAt,
        ]);

        $query->get();

        // Assert that we ended up here.
        $this->assertTrue(true);
    }
}
