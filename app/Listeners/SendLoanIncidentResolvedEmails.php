<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\LoanIncidentResolvedEvent;
use App\Mail\LoanIncidentResolved;
use App\Mail\LoanIncidentReviewable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanIncidentResolvedEmails
{
    public function handle(LoanIncidentResolvedEvent $event)
    {
        $loan = $event->incident->loan;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        Mail::to(
            $owner->user->email,
            $owner->user->name . " " . $owner->user->last_name
        )->queue(new LoanIncidentResolved($event->incident, $loan, $owner));

        Mail::to(
            $borrower->user->email,
            $borrower->user->name . " " . $borrower->user->last_name
        )->queue(new LoanIncidentResolved($event->incident, $loan, $borrower));
    }
}
