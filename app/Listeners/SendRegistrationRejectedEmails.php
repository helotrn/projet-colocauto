<?php

namespace App\Listeners;

use App\Events\RegistrationRejectedEvent;
use App\Mail\Registration\Rejected as RegistrationRejected;
use App\Models\User;
use Mail;

class SendRegistrationRejectedEmails
{
    public function handle(RegistrationRejectedEvent $event)
    {
        $user = $event->user;

        Mail::to($user->email, $user->full_name)->queue(
            new RegistrationRejected($user)
        );
    }
}
