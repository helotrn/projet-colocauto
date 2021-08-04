<?php

namespace App\Listeners;

use App\Events\LoanCreatedEvent;
use App\Events\RegistrationApprovedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateNokeUserIfNotExists
{
    public function handle($event)
    {
        if ($event instanceof LoanCreatedEvent) {
            if ($event->loan->loanable->padlock) {
                $event->loan->borrower->user->getNokeUser();
            }
        }

        if ($event instanceof RegistrationApprovedEvent) {
            $event->user->getNokeUser();
        }
    }
}
