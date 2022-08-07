<?php

namespace App\Listeners;

use App\Mail\Loan\HandoverContestationResolved as LoanHandoverContestationResolved;
use App\Models\Handover;
use App\Models\User;
use App\Events\LoanHandoverContestationResolvedEvent;
use Mail;

class SendLoanHandoverContestationResolvedEmails
{
    /*
       Send contestation-resolved notification to owner and borrower.
       Only send one copy if owner is borrower.

       These rules apply for on-demand as well as self-service vehicles.
    */
    public function handle(LoanHandoverContestationResolvedEvent $event)
    {
        $loan = $event->handover->loan;
        $admin = $event->user;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        Mail::to($borrower->user->email, $borrower->user->full_name)->queue(
            new LoanHandoverContestationResolved(
                $event->handover,
                $loan,
                $borrower->user,
                $admin
            )
        );

        if ($owner && $owner->user->id !== $borrower->user->id) {
            Mail::to($owner->user->email, $owner->user->full_name)->queue(
                new LoanHandoverContestationResolved(
                    $event->handover,
                    $loan,
                    $owner->user,
                    $admin
                )
            );
        }
    }
}
