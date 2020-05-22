<?php

namespace App\Listeners;

use App\Events\LoanCreatedEvent;
use App\Mail\LoanCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanCreatedEmails
{
    public function handle(LoanCreatedEvent $event) {
        $owner = $event->loan->loanable->owner;

        if (!$owner) {
            return;
        }

        $borrower = $event->loan->borrower;
        Mail::to($owner->user->email, $owner->name . ' ' . $owner->last_name)
          ->queue(new LoanCreated($borrower, $owner, $event->loan));
    }
}
