<?php

namespace App\Listeners;

use App\Mail\Loan\HandoverContested as LoanHandoverContested;
use App\Mail\Loan\HandoverReviewable as LoanHandoverReviewable;
use App\Models\Handover;
use App\Models\User;
use App\Events\LoanHandoverContestedEvent;
use Mail;

class SendLoanHandoverContestedEmails
{
    /*
       Send loan-handover-contested notification to:
         - owner if the borrower has contested
         - borrower if the owner has contested

       Also notify admins because they are the only ones who can resolve contestations.

       These rules apply for on-demand as well as self-service vehicles.
    */
    public function handle(LoanHandoverContestedEvent $event)
    {
        $loan = $event->handover->loan;
        $caller = $event->user;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        if ($caller->id !== $borrower->user->id) {
            Mail::to($borrower->user->email, $borrower->user->full_name)->queue(
                new LoanHandoverContested(
                    $event->handover,
                    $loan,
                    $borrower->user,
                    $caller
                )
            );
        }

        if ($owner && $caller->id !== $owner->user->id) {
            Mail::to($owner->user->email, $owner->user->full_name)->queue(
                new LoanHandoverContested(
                    $event->handover,
                    $loan,
                    $owner->user,
                    $caller
                )
            );
        }

        $admins = User::whereRole("admin")
            ->select("name", "last_name", "email")
            ->get()
            ->toArray();
        $communityAdmins = $loan->community
            ->users()
            ->select("name", "last_name", "email")
            ->where("community_user.role", "admin")
            ->get()
            ->toArray();

        foreach (array_merge($admins, $communityAdmins) as $admin) {
            Mail::to(
                $admin["email"],
                $admin["name"] . " " . $admin["last_name"]
            )->queue(
                new LoanHandoverReviewable($event->handover, $loan, $caller)
            );
        }
    }
}
