<?php

namespace App\Listeners;

use App\Events\LoanExtensionCreatedEvent;
use App\Mail\LoanExtensionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanExtensionCreatedEmails
{
    public function handle(LoanExtensionCreatedEvent $event)
    {
        $loan = $event->extension->loan;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        if (!$owner || !$owner->user) {
            return;
        }

        Mail::to(
            $owner->user->email,
            $owner->user->name . " " . $owner->user->last_name
        )->queue(
            new LoanExtensionCreated(
                $event->extension,
                $loan,
                $borrower,
                $owner
            )
        );
    }
}
