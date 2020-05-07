<?php

namespace App\Listeners;

use App\Events\LoanIntentionRejectedEvent;
use App\Mail\LoanIntentionRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanIntentionRejectedEmails
{
    public function handle(LoanIntentionRejectedEvent $event) {
        $loan = $event->intention->loan;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        Mail::to(
            $borrower->user->email,
            $borrower->user->name . ' ' . $borrower->user->last_name
        )->queue(
            new LoanIntentionRejected(
                $event->intention,
                $loan,
                $borrower,
                $owner
            )
        );
    }
}
