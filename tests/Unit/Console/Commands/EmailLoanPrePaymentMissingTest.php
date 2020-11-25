<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\EmailLoanPrePaymentMissing as EmailLoanPrePaymentMissingCommand;
use App\Models\Loan;
use Carbon\Carbon;

use Tests\TestCase;

class EmailLoanPrePaymentMissingTest extends TestCase
{
    public function testgetQuery() {
        $createdAt = (new Carbon())->subtract(3, 'hours');

        $query = EmailLoanPrePaymentMissingCommand::getQuery(
            [ 'created_at' => $createdAt ]
        );

        $query->get();

                             // Assert that we ended up here.
        $this->assertTrue(true);
    }
}
