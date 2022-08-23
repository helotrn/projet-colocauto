<?php

namespace App\Listeners;

use App\Events\LoanIntentionRejectedEvent;
use App\Mail\LoanIntentionRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanIntentionRejectedEmails
{
    /*
       Send loan-intention-rejected notification to borrower if loanable is not
       self-service and borrower is not also the owner.
    */
    public function handle(LoanIntentionRejectedEvent $event)
    {
        $loan = $event->intention->loan;
        $loanable = $loan->loanable;
        $owner = $loanable->owner;
        $borrower = $loan->borrower;

        if (
            !$loanable->is_self_service &&
            $owner &&
            $owner->user->id !== $borrower->user->id
        ) {
            Mail::to($borrower->user->email, $borrower->user->full_name)->queue(
                new LoanIntentionRejected(
                    $event->intention,
                    $loan,
                    $borrower,
                    $owner
                )
            );
        }
    }
}
