<?php

namespace App\Listeners;

use App\Events\BorrowerCompletedEvent;
use App\Mail\Borrower\Completed as BorrowerCompleted;
use App\Mail\Borrower\Reviewable as BorrowerReviewable;
use App\Models\User;
use Mail;

class SendBorrowerCompletedEmails
{
    public function handle(BorrowerCompletedEvent $event) {
        $user = $event->user;

        if (!isset($user->meta['sent_borrower_completed_email'])) {
            Mail::to($user->email, $user->name . ' ' . $user->last_name)
                ->queue(new BorrowerCompleted($user));

            $user->forceFill([
                'meta' => [ 'sent_borrower_completed_email' => true ],
            ])->save();

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
                      ->queue(new BorrowerReviewable($event->user, $community));
                }
            }
        }
    }
}
