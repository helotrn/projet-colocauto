<?php

namespace App\Listeners;

use App\Events\LoanableCreatedEvent;
use App\Mail\LoanableCreated;
use App\Mail\LoanableReviewable;
use App\Models\User;
use Mail;

class SendLoanableCreatedEmails
{
    public function handle(LoanableCreatedEvent $event)
    {
        Mail::to(
            $event->user->email,
            $event->user->name . " " . $event->user->last_name
        )->queue(new LoanableCreated($event->user, $event->loanable));

        $admins = User::whereRole("admin")
            ->select("name", "last_name", "email")
            ->get()
            ->toArray();
        foreach ($event->user->communities as $community) {
            $communityAdmins = $community
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
                    new LoanableReviewable(
                        $event->user,
                        $community,
                        $event->loanable
                    )
                );
            }
        }
    }
}
