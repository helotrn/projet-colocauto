<?php

namespace App\Listeners;

use App\Events\BorrowerApprovedEvent;
use App\Mail\Borrower\Approved as BorrowerApproved;
use Mail;

class SendBorrowerApprovedEmails
{
    public function handle(BorrowerApprovedEvent $event) {
        $user = $event->user;

        if (!isset($user->meta['sent_borrower_approved_email'])) {
            Mail::to($user->email, $user->name . ' ' . $user->last_name)
                ->queue(new BorrowerApproved($user));

            $meta = $user->meta;
            $meta['sent_borrower_approved_email'] = true;
            $user->meta = $meta;

            $user->save();
        }
    }
}
