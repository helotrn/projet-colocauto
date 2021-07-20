<?php

namespace App\Listeners;

use App\Events\LoanExtensionAcceptedEvent;
use App\Mail\LoanExtensionAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanExtensionAcceptedEmails
{
    public function handle(LoanExtensionAcceptedEvent $event)
    {
        $loan = $event->extension->loan;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        Mail::to(
            $borrower->user->email,
            $borrower->user->name . " " . $borrower->user->last_name
        )->queue(
            new LoanExtensionAccepted(
                $event->extension,
                $loan,
                $borrower,
                $owner
            )
        );
    }
}
