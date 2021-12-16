<?php

namespace App\Listeners;

use App\Events\LoanIntentionAcceptedEvent;
use App\Mail\LoanIntentionAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanIntentionAcceptedEmails
{
    /*
       Send loan-intention-accepted notification to borrower if loanable is not
       self-service and borrower is not also the owner.
    */
    public function handle(LoanIntentionAcceptedEvent $event)
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
            Mail::to(
                $borrower->user->email,
                $borrower->user->name . " " . $borrower->user->last_name
            )->queue(
                new LoanIntentionAccepted(
                    $event->intention,
                    $loan,
                    $borrower,
                    $owner
                )
            );
        }
    }
}
