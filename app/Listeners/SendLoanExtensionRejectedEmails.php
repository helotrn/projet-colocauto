<?php

namespace App\Listeners;

use App\Events\LoanExtensionRejectedEvent;
use App\Mail\LoanExtensionRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanExtensionRejectedEmails
{
    /*
       Send loan-extension-rejected notification to borrower if loanable is
       not self service and borrower is not also the owner.
    */
    public function handle(LoanExtensionRejectedEvent $event)
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
            Mail::to(
                $borrower->user->email,
                $borrower->user->name . " " . $borrower->user->last_name
            )->queue(
                new LoanExtensionRejected(
                    $event->extension,
                    $loan,
                    $borrower,
                    $owner
                )
            );
        }
    }
}
