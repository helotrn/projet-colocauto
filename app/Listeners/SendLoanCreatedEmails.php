<?php

namespace App\Listeners;

use App\Events\LoanCreatedEvent;
use App\Mail\Loan\Created as LoanCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanCreatedEmails
{
    /*
       Send loan-created notification to owner if loanable is not self-service
       and borrower is not also the owner.
    */
    public function handle(LoanCreatedEvent $event)
    {
        $loan = $event->loan;
        $loanable = $loan->loanable;
        $owner = $loanable->owner;
        $borrower = $loan->borrower;
        $coowners = [];
        if( $loanable->coowners ) {
            // also notify coowners if they want
            $coowners = $loanable->coowners->map( function($coowner){
                return $coowner->receive_notifications ? $coowner->user->email : null;
            } )->filter()->all();
        }

        if (
            !$loanable->is_self_service && $owner &&
            (
                ($owner->user->id !== $borrower->user->id) ||
                sizeof($coowners) > 0
            )
        ) {
            Mail::to($owner->user->email, $owner->user->full_name)
                ->cc($coowners)
                ->queue(
                    new LoanCreated($borrower, $owner, $event->loan)
                );
        }
    }
}
