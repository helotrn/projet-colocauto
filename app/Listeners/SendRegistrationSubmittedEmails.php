<?php

namespace App\Listeners;

use App\Events\RegistrationSubmittedEvent;
use App\Mail\Registration\Submitted as RegistrationSubmitted;
use App\Mail\Registration\Reviewable as RegistrationReviewable;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendRegistrationSubmittedEmails
{
    public function handle(RegistrationSubmittedEvent $event)
    {
        // Send the email to the user if that's the first time he registers
        if (!isset($event->user->meta["sent_registration_submitted_email"])) {
            Mail::mailer("mandrill")
                ->to($event->user->email, $event->user->full_name)
                ->queue(new RegistrationSubmitted($event->user));

            // Save Meta
            $meta = $event->user->meta;
            $meta["sent_registration_submitted_email"] = true;
            $event->user->meta = $meta;
            $event->user->save();
        }

        // Notify the admins.
        foreach ($event->user->main_community->admins() as $admin) {
            Mail::to($admin->email, $admin->full_name)->queue(
                new RegistrationReviewable(
                    $event->user,
                    $event->user->main_community
                )
            );
        }
    }
}
