<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\NokeSyncLoans as NokeSyncLoansCommand;
use App\Models\Loan;
use Carbon\Carbon;

use Tests\TestCase;

class NokeSyncLoansTest extends TestCase
{
    public function testGetLoansFromPadlockMacQuery()
    {
        $query = NokeSyncLoansCommand::getLoansFromPadlockMacQuery([
            "mac_address" => "0D:34:F2:3E:0F:2F",
        ]);

        $query->get();

        // Assert that we ended up here.
        $this->assertTrue(true);
    }
}
