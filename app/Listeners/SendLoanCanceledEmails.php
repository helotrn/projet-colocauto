<?php

namespace App\Listeners;

use App\Events\Loan\CanceledEvent;
use App\Mail\Loan\Canceled as LoanCanceled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanCanceledEmails
{
    /*
       For on-demand vehicles:
         Whoever cancels the loan, then the counterpart should be notified.

       For self-service vehicles
         If the loan is cancelled by the owner, then the borrower should be notified.
         If the loan is cancelled by the borrower, it is not necessary to notify the owner.
    */
    public function handle(CanceledEvent $event)
    {
        $sender = $event->user;
        $loan = $event->loan;
        $loanable = $loan->loanable;
        $owner = $loanable->owner;
        $borrower = $loan->borrower;

        if (
            !$loanable->is_self_service &&
            $owner &&
            $owner->user->id !== $sender->id
        ) {
            Mail::to($owner->user->email, $owner->user->full_name)->queue(
                new LoanCanceled($sender, $owner->user, $loan)
            );
        }

        if ($borrower && $borrower->user->id !== $sender->id) {
            Mail::to($borrower->user->email, $borrower->user->full_name)->queue(
                new LoanCanceled($sender, $borrower->user, $loan)
            );
        }
    }
}
