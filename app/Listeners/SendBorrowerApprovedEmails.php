<?php

namespace App\Listeners;

use App\Events\BorrowerApprovedEvent;
use App\Mail\Borrower\Approved as BorrowerApproved;
use App\Mail\Borrower\Pending as BorrowerPending;
use Illuminate\Support\Facades\Mail;

/*
  This listener will:
    - Send an confirmation email to the user
      - As borrower approved if registration is approved
      - As borrower pending if registration is not yet approved
*/

class SendBorrowerApprovedEmails
{
    public function handle(BorrowerApprovedEvent $event)
    {
        $user = $event->user;

        if (!isset($user->meta["sent_borrower_approved_email"])) {
            if (!isset($user->meta["sent_registration_approved_email"])) {
                // Registration is not yet approved, borrower pending
                Mail::to($user->email, $user->full_name)->queue(
                    new BorrowerPending(
                        $user,
                        isset($user->meta["sent_registration_submitted_email"])
                    )
                );
            } else {
                // Registration is approved, borrower approved
                Mail::to($user->email, $user->full_name)->queue(
                    new BorrowerApproved($user)
                );
            }

            // Mark the user email approved as borrower
            $meta = $user->meta;
            $meta["sent_borrower_approved_email"] = true;
            $user->meta = $meta;

            $user->save();
        }
    }
}
