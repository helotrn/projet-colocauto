<?php

namespace App\Listeners;

use App\Events\RegistrationApprovedEvent;
use App\Mail\Registration\Approved as RegistrationApproved;
use App\Models\User;
use Mail;

class SendRegistrationApprovedEmails
{
    public function handle(RegistrationApprovedEvent $event)
    {
        $user = $event->user;

        if (!isset($user->meta["sent_registration_approved_email"])) {
            
            Mail::mailer("mandrill")
                ->to($user->email, $user->name . " " . $user->last_name)
                ->queue(new RegistrationApproved($user));

            // Save Meta
            $meta = $user->meta;
            $meta["sent_registration_approved_email"] = true;
            $user->meta = $meta;
            $user->save();
        }
    }
}
