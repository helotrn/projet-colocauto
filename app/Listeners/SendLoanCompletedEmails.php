<?php

namespace App\Listeners;

use App\Events\LoanCompletedEvent;
use App\Mail\Loan\LoanCompleted;
use App\Models\Loan;
use App\Models\User;
use Mail;

class SendLoanCompletedEmails
{
    public function handle(LoanCompletedEvent $event)
    {
        $loan = $event->loan;

        self::sendMail($loan->borrower->user, $loan, false);
    }

    private static function sendMail(User $user, Loan $loan, bool $isOwner)
    {
        Mail::to($user, $user->full_name)
            ->queue(new LoanCompleted($user, $loan, $isOwner));
    }
}
