<?php

namespace App\Listeners;

use App\Events\LoanIntentionAcceptedEvent;
use App\Mail\LoanIntentionAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanIntentionAcceptedEmails
{
    public function handle(LoanIntentionAcceptedEvent $event) {
        $loan = $event->intention->loan;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        Mail::to(
            $borrower->user->email,
            $borrower->user->name . ' ' . $borrower->user->last_name
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
