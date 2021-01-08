<?php

namespace App\Listeners;

use App\Mail\Loan\HandoverContestationResolved as LoanHandoverContestationResolved;
use App\Models\Handover;
use App\Models\User;
use App\Events\LoanHandoverContestationResolvedEvent;
use Mail;

class SendLoanHandoverContestationResolvedEmails
{
    public function handle(LoanHandoverContestationResolvedEvent $event) {
        $loan = $event->handover->loan;
        $admin = $event->user;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        Mail::to(
            $borrower->user->email,
            $borrower->user->name . ' ' . $borrower->user->last_name
        )->queue(new LoanHandoverContestationResolved(
            $event->handover,
            $loan,
            $borrower->user,
            $admin
        ));

        if ($owner) {
            Mail::to(
                $owner->user->email,
                $owner->user->name . ' ' . $owner->user->last_name
            )->queue(new LoanHandoverContestationResolved(
                $event->handover,
                $loan,
                $owner->user,
                $admin
            ));
        }
    }
}
