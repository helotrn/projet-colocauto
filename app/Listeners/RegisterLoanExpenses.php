<?php

namespace App\Listeners;

use App\Events\LoanCompletedEvent;

class RegisterLoanExpenses
{
    public function handle(LoanCompletedEvent $event)
    {
        $event->loan->writeExpenses();
    }
}
