<?php

namespace App\Listeners;

use App\Events\BorrowerCompletedEvent;
use App\Mail\Borrower\Completed as BorrowerCompleted;
use App\Mail\Borrower\Reviewable as BorrowerReviewable;
use App\Models\User;
use Mail;

/*
  This listener will send an email to the user.
  It will then send a notification to global admins.
  It will then finally try to find admins in the user's communities to whom to
  send a notification.
*/
class SendBorrowerCompletedEmails
{
    public function handle(BorrowerCompletedEvent $event) {
        $user = $event->user;
        $communities = $user->communities;


                             // Don't send if already sent.
        if (!isset($user->meta['sent_borrower_completed_email'])) {
                             // Send confirmation to borrower.
            Mail::to($user->email, $user->name.' '.$user->last_name)
                ->queue(new BorrowerCompleted($user));


                             // Send a notification to all global admins.
            $global_admins = User::whereRole('admin')
                ->select('name', 'last_name', 'email')->get()
                ->toArray();

            foreach ($global_admins as $admin) {
                Mail::to($admin['email'], $admin['name'] . ' ' . $admin['last_name'])
                  ->queue(new BorrowerReviewable($event->user, $communities));
            }


                             // As we try to find community admins for the user
                             // we try to avoid sending to the same community
                             // twice. As an example when the user is member of
                             // both a community and it's parent.
            $sent_to_communities = [];
            $found_admins = false;

                             // For all user communities.
            foreach ($communities as $community) {
                             // While we go up the chain of parents...
                while ($community) {
                    $community_id = $community['id'];

                             // Did we check this community already?
                    if (in_array($community_id, $sent_to_communities)) {
                        break;
                    }
                    $sent_to_communities[] = $community_id;


                             // Does this community have admins?
                    $community_admins = $community->users()
                        ->select('name', 'last_name', 'email')
                        ->where('community_user.role', 'admin')->get()
                        ->toArray();

                    foreach ($community_admins as $admin) {
                        Mail::to($admin['email'], $admin['name'].' '.$admin['last_name'])
                          ->queue(new BorrowerReviewable($event->user, $community));
                    }

                    if (!empty($community_admins)) {
                        $found_admins = true;
                        break;
                    }


                             // Does this community have a parent?
                    $community = $community->parent;
                }

                if ($found_admins) {
                    break;
                }
            }

                             // Mark the emails as sent.
            $meta = $user->meta;
            $meta['sent_borrower_completed_email'] = true;
            $user->meta = $meta;

            $user->save();
        }
    }
}
