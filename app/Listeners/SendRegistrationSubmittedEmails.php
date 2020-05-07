<?php

namespace App\Listeners;

use App\Events\RegistrationSubmittedEvent;
use App\Mail\RegistrationSubmitted;
use App\Mail\RegistrationReviewable;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendRegistrationSubmittedEmails
{
    public function handle(RegistrationSubmittedEvent $event) {
        Mail::to($event->user->email, $event->user->name . ' ' . $event->user->last_name)
          ->queue(new RegistrationSubmitted($event->user));

        $admins = User::whereRole('admin')
            ->select('name', 'last_name', 'email')->get()
            ->toArray();
        foreach ($event->user->communities as $community) {
            $communityAdmins = $community->users()
                ->select('name', 'last_name', 'email')
                ->where('community_user.role', 'admin')->get()
                ->toArray();

            foreach (array_merge($admins, $communityAdmins) as $admin) {
                Mail::to($admin['email'], $admin['name'] . ' ' . $admin['last_name'])
                  ->queue(new RegistrationReviewable($event->user, $community));
            }
        }
    }
}
