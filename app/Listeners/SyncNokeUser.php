<?php

namespace App\Listeners;

use App\Events\LoanCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncNokeUser
{
    public function handle(LoanCreatedEvent $event) {
        if ($event->loan->loanable->padlock) {
            $event->loan->borrower->user->getNokeUser();
        }
    }
}
