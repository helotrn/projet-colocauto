<?php

namespace App\Listeners;

use App\Events\LoanModifiedEvent;
use App\Mail\Loan\Modified as LoanModified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanModifiedEmails
{
    /*
       Send loan-modified notification to owner if loanable is not self-service
       and borrower is not also the owner.
    */
    public function handle(LoanModifiedEvent $event)
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
            Mail::to($owner->user->email, $owner->user->full_name)->queue(
                new LoanModified($borrower, $owner, $event->loan)
            );
        }
    }
}
