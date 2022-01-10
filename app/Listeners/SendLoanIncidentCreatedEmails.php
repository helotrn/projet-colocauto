<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\LoanIncidentCreatedEvent;
use App\Mail\LoanIncidentCreated;
use App\Mail\LoanIncidentReviewable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendLoanIncidentCreatedEmails
{
    /*
       Send loan-incident-created notification to owner if not also the
       borrower.

       Also notify admins because they are the only ones who can resolve incidents.

       These rules apply for on-demand as well as self-service vehicles.
    */
    public function handle(LoanIncidentCreatedEvent $event)
    {
        $loan = $event->incident->loan;
        $borrower = $loan->borrower;
        $owner = $loan->loanable->owner;

        if ($owner && $owner->user->id !== $borrower->user->id) {
            Mail::to(
                $owner->user->email,
                $owner->user->name . " " . $owner->user->last_name
            )->queue(
                new LoanIncidentCreated(
                    $event->incident,
                    $loan,
                    $borrower,
                    $owner
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
                new LoanIncidentReviewable(
                    $event->incident,
                    $loan,
                    $borrower,
                    $owner,
                    $loan->community
                )
            );
        }
    }
}
