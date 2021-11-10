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
        
        // Send a welcome email to the user
        Mail::to(
            $event->user->email,
            $event->user->name . " " . $event->user->last_name
        )->queue(new RegistrationSubmitted($event->user));
        
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
            
                // Send an email notification to each admin 
            foreach (array_merge($admins, $communityAdmins) as $admin) {
                Mail::to(
                    $admin["email"],
                    $admin["name"] . " " . $admin["last_name"]
                )->queue(new RegistrationReviewable($event->user, $community));
            }
        }
    }
}
