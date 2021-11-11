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
        
        if (!isset($event->user->meta["sent_registration_submitted_email"])) {
            // Send an email to the user
            Mail::mailer("mandrill")
                ->to($event->user->email, $event->user->full_name)
                ->queue(new RegistrationSubmitted($event->user));

            // Save Meta
            $meta = $event->user->meta;
            $meta["sent_registration_submitted_email"] = true;
            $event->user->meta = $meta;
            $event->user->save();
        }
        
        // Retrieve all super admins
        $admins = User::whereRole("admin")
            ->select("name", "last_name", "email")
            ->get()
            ->toArray();

        // Retrieve the admins for each community 
        foreach ($event->user->communities as $community) {
            $communityAdmins = $community
                ->users()
                ->select("name", "last_name", "email")
                ->where("community_user.role", "admin")
                ->get()
                ->toArray();
            
            // Send an email notification to all admins 
            foreach (array_merge($admins, $communityAdmins) as $admin) {
                Mail::to(
                    $admin["email"],
                    $admin["name"] . " " . $admin["last_name"]
                )->queue(new RegistrationReviewable($event->user, $community));
            }
        }
    }
}
