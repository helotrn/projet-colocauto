<?php

namespace App\Listeners;

use App\Models\Handover;
use App\Events\LoanHandoverContestedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanHandoverContestedEmails
{
    public function handle(LoanHandoverContestedEvent $event) {
        $loan = $event->handover->loan;
        $caller = $event->user;
        $borrower = $loan->loanable->borrower;
        $owner = $loan->loanable->owner;

        if ($caller->id !== $borrower->user->id) {
            Mail::to(
                $borrower->user->email,
                $borrower->user->name . ' ' . $borrower->user->last_name
            )->queue(new LoanHandoverContested($event->handover, $loan, $caller));
        }

        if ($owner && $caller->id !== $owner->user->id) {
            Mail::to(
                $owner->user->email,
                $owner->user->name . ' ' . $owner->user->last_name
            )->queue(new LoanHandoverContested($event->handover, $loan, $caller));
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
                ->queue(new LoanHandoverReviewable(
                    $event->handover,
                    $loan,
                    $caller
                ));
        }
    }
}
