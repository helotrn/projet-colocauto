<?php

namespace App\Listeners;

use App\Events\LoanCreatedEvent;
use App\Mail\Loan\Created as LoanCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanCreatedEmails
{
    /*
       Send loan-created notification to owner if loanable is not self-service
       and borrower is not also the owner.
    */
    public function handle(LoanCreatedEvent $event)
    {
        $loan = $event->loan;
        $loanable = $loan->loanable;
        $owner = $loanable->owner;
        $borrower = $loan->borrower;

        if (
            !$loanable->is_self_service &&
            $owner &&
            $owner->user->id !== $borrower->user->id
        ) {
            Mail::to(
                $owner->user->email,
                $owner->user->name . " " . $owner->user->last_name
            )->queue(new LoanCreated($borrower, $owner, $event->loan));
        }
    }
}
