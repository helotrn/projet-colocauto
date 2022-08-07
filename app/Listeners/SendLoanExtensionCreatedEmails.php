<?php

namespace App\Listeners;

use App\Events\LoanExtensionCreatedEvent;
use App\Mail\LoanExtensionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanExtensionCreatedEmails
{
    /*
       Send loan-extension-created notification to borrower if loanable is
       not self service and borrower is not also the owner.
    */
    public function handle(LoanExtensionCreatedEvent $event)
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
            Mail::to($owner->user->email, $owner->user->full_name)->queue(
                new LoanExtensionCreated(
                    $event->extension,
                    $loan,
                    $borrower,
                    $owner
                )
            );
        }
    }
}
