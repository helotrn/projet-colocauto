<?php

namespace App\Listeners;

use App\Events\LoanExtensionAcceptedEvent;
use App\Mail\LoanExtensionAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanExtensionAcceptedEmails
{
    /*
       Send loan-extension-accepted notification to borrower if loanable is
       not self service and borrower is not also the owner.
    */
    public function handle(LoanExtensionAcceptedEvent $event)
    {
        $loan = $event->extension->loan;
        $loanable = $loan->loanable;
        $borrower = $loan->borrower;
        $owner = $loanable->owner;

        if (
            !$loanable->is_self_service &&
            $owner &&
            $owner->user->id !== $borrower->user->id
        ) {
            Mail::to($borrower->user->email, $borrower->user->full_name)->queue(
                new LoanExtensionAccepted(
                    $event->extension,
                    $loan,
                    $borrower,
                    $owner
                )
            );
        }
    }
}
