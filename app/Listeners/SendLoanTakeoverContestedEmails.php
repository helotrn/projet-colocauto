<?php

namespace App\Listeners;

use App\Mail\Loan\TakeoverContested as LoanTakeoverContested;
use App\Mail\Loan\TakeoverReviewable as LoanTakeoverReviewable;
use App\Models\Takeover;
use App\Models\User;
use App\Events\LoanTakeoverContestedEvent;
use Mail;

class SendLoanTakeoverContestedEmails
{
    public function handle(LoanTakeoverContestedEvent $event) {
        $loan = $event->takeover->loan;
        $caller = $event->user;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        if ($caller->id !== $borrower->user->id) {
            Mail::to(
                $borrower->user->email,
                $borrower->user->name . ' ' . $borrower->user->last_name
            )->queue(new LoanTakeoverContested(
                $event->takeover,
                $loan,
                $borrower->user,
                $caller
            ));
        }

        if ($owner && $caller->id !== $owner->user->id) {
            Mail::to(
                $owner->user->email,
                $owner->user->name . ' ' . $owner->user->last_name
            )->queue(new LoanTakeoverContested(
                $event->takeover,
                $loan,
                $owner->user,
                $caller
            ));
        }

        $admins = User::whereRole('admin')
            ->select('name', 'last_name', 'email')->get()
            ->toArray();
        $communityAdmins = $loan->community->users()
            ->select('name', 'last_name', 'email')
            ->where('community_user.role', 'admin')->get()
            ->toArray();

        foreach (array_merge($admins, $communityAdmins) as $admin) {
            Mail::to($admin['email'], $admin['name'] . ' ' . $admin['last_name'])
                ->queue(new LoanTakeoverReviewable(
                    $event->takeover,
                    $loan,
                    $caller
                ));
        }
    }
}
