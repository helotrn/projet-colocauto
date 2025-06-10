<?php

namespace App\Listeners;

use App\Events\LoanToCompleteEvent;
use App\Mail\Loan\ToComplete as LoanToComplete;
use App\Models\Loan;
use App\Models\User;
use Mail;

class SendLoanToCompleteEmails
{
    public function handle(LoanToCompleteEvent $event)
    {
        $loan = $event->loan;

        self::sendMail($loan->borrower->user, $loan, false);
    }

    private static function sendMail(User $user, Loan $loan, bool $isOwner)
    {
        Mail::to($user, $user->full_name)
            ->queue(new LoanToComplete($user, $loan, $isOwner));
    }
}
