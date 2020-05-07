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
        $borrower = $event->loan->borrower;
        Mail::to($owner->user->email, $owner->name . ' ' . $owner->full_name)
          ->queue(new LoanCreated($borrower, $owner, $event->loan));
    }
}
