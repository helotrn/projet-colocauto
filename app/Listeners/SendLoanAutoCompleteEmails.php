<?php

namespace App\Listeners;

use App\Events\LoanAutoCompleteEvent;
use App\Mail\Loan\AutoComplete as LoanAutoComplete;
use App\Models\Loan;
use App\Models\User;
use Mail;

class SendLoanAutoCompleteEmails
{
    public function handle(LoanAutoCompleteEvent $event)
    {
        $loan = $event->loan;

        self::sendMail($loan->borrower->user, $loan, false);
    }

    private static function sendMail(User $user, Loan $loan, bool $isOwner)
    {
        Mail::to($user, $user->full_name)
            ->queue(new LoanAutoComplete($user, $loan, $isOwner));
    }
}
