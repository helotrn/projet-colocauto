<?php

namespace App\Listeners;

use App\Mail\Loan\TakeoverContestationResolved as LoanTakeoverContestationResolved;
use App\Models\Takeover;
use App\Models\User;
use App\Events\LoanTakeoverContestationResolvedEvent;
use Mail;

class SendLoanTakeoverContestationResolvedEmails
{
    /*
       Send contestation-resolved notification to owner and borrower.
       Only send one copy if owner is borrower.

       These rules apply for on-demand as well as self-service vehicles.
    */
    public function handle(LoanTakeoverContestationResolvedEvent $event)
    {
        $loan = $event->takeover->loan;
        $admin = $event->user;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        Mail::to(
            $borrower->user->email,
            $borrower->user->name . " " . $borrower->user->last_name
        )->queue(
            new LoanTakeoverContestationResolved(
                $event->takeover,
                $loan,
                $borrower->user,
                $admin
            )
        );

        if ($owner && $owner->user->id !== $borrower->user->id) {
            Mail::to(
                $owner->user->email,
                $owner->user->name . " " . $owner->user->last_name
            )->queue(
                new LoanTakeoverContestationResolved(
                    $event->takeover,
                    $loan,
                    $owner->user,
                    $admin
                )
            );
        }
    }
}
