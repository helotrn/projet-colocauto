<?php

namespace App\Listeners;

use App\Events\BorrowerSuspendedEvent;
use App\Mail\Borrower\Suspended as BorrowerSuspended;
use Illuminate\Support\Facades\Mail;

/*
  This listener will:
    - Send an confirmation email to the user
*/

class SendBorrowerSuspendedEmails
{
    public function handle(BorrowerSuspendedEvent $event)
    {
        $user = $event->user;

        Mail::to($user->email, $user->full_name)->queue(
            new BorrowerSuspended($user)
        );
    }
}
