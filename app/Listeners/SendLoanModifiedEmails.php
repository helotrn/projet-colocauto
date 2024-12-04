<?php

namespace App\Listeners;

use App\Events\LoanModifiedEvent;
use App\Mail\Loan\Modified as LoanModified;
use App\Mail\Loan\ModifiedForBorrower as LoanModifiedForBorrower;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanModifiedEmails
{
    /*
       Send loan-modified notification to users that are concerned
    */
    public function handle(LoanModifiedEvent $event)
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

        // notify the owner if it's not the borrower
        if (
            !$loanable->is_self_service &&
            $owner &&
            (
                ($owner->user->id !== $borrower->user->id) ||
                sizeof($coowners) > 0
            )
        ) {
            Mail::to($owner->user->email, $owner->user->full_name)
                ->cc($coowners)
                ->queue(
                    new LoanModified($borrower, $owner, $event->loan)
                );
        }

        // notify the borrower if the modification is done by someone else
        if(
            !$loanable->is_self_service &&
            $event->user->id !== $borrower->user->id
        ) {
            Mail::to($borrower->user->email, $borrower->user->full_name)->queue(
                new LoanModifiedForBorrower($borrower, $owner, $event->loan, $event->user)
            );
        }
    }
}
