<?php

namespace App\Listeners;

use App\Events\BorrowerCompletedEvent;
use App\Mail\Borrower\Completed as BorrowerCompleted;
use App\Mail\Borrower\Reviewable as BorrowerReviewable;
use App\Services\MattermostNotificationsService as MattermostNotifications;
use App\Models\User;
use Mail;

/*
  This event will:
    - Send an confirmation email to the user
    - Send an email to admins
    - Create a Mattermost Notification
*/

class SendBorrowerCompletedEmails
{
    public function handle(BorrowerCompletedEvent $event)
    {
        $user = $event->user;
        $communities = $user->communities;
        // And never receive the email notification
        if (!isset($user->meta["sent_borrower_completed_email"])) {
            /**
             * USER
             */

            // Send the email confirmation to the borrower.
            Mail::to($user->email, $user->full_name)->queue(
                new BorrowerCompleted($user)
            );

            // Mark the email as sent.
            $meta = $user->meta;
            $meta["sent_borrower_completed_email"] = true;
            $user->meta = $meta;
            $user->save();

            /**
             * ADMIN
             */

            MattermostNotifications::send(
                $user->full_name .
                    " a complété son dossier de conduite " .
                    $user->admin_link
            );

            // Send a notification to all admins.
            $admins = User::whereRole("admin")
                ->select("name", "last_name", "email")
                ->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email, $admin->full_name)->queue(
                    new BorrowerReviewable($event->user, $communities)
                );
            }
        }
    }
}
