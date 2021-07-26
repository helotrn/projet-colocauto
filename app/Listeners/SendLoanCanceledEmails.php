<?php

namespace App\Listeners;

use App\Events\Loan\CanceledEvent;
use App\Mail\Loan\Canceled as LoanCanceled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanCanceledEmails
{
    public function handle(CanceledEvent $event)
    {
        $sender = $event->user;
        $loan = $event->loan;
        $owner = $loan->loanable->owner;
        $borrower = $loan->borrower;

        if ($owner && $owner->user->id !== $sender->id) {
            Mail::to(
                $owner->user->email,
                $owner->user->name . " " . $owner->user->last_name
            )->queue(new LoanCanceled($sender, $owner->user, $loan));
        }

        if ($borrower && $borrower->user->id !== $sender->id) {
            Mail::to(
                $borrower->user->email,
                $borrower->user->name . " " . $borrower->user->last_name
            )->queue(new LoanCanceled($sender, $borrower->user, $loan));
        }
    }
}
