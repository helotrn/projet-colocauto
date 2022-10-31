<?php

namespace App\Listeners;

use App\Events\LoanCompletedEvent;
use App\Mail\Loan\LoanCompleted;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendLoanCompletedEmails
{
    public function handle(LoanCompletedEvent $event)
    {
        $loan = $event->loan;

        self::sendMail($loan->borrower->user, $loan, false);

        if (
            !$loan->loanable->is_self_service &&
            $loan->loanable->owner &&
            $loan->loanable->owner->user->id != $loan->borrower->user->id
        ) {
            self::sendMail($loan->loanable->owner->user, $loan, true);
        }
    }

    private static function sendMail(User $user, Loan $loan, bool $isOwner)
    {
        Mail::mailer("mandrill")
            ->to($user, $user->full_name)
            ->queue(new LoanCompleted($user, $loan, $isOwner));
    }
}
