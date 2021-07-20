<?php

namespace App\Listeners;

use App\Events\LoanExtensionRejectedEvent;
use App\Mail\LoanExtensionRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanExtensionRejectedEmails
{
    public function handle(LoanExtensionRejectedEvent $event)
    {
        $loan = $event->extension->loan;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

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
