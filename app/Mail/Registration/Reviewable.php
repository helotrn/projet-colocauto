<?php

/**
 * App\Mail\Registration\Reviewable
 *
 * Event triggered when the user has completed its registration.
 * Send a notification to the community administrators so they can approve the registration.
 *
 */

namespace App\Mail\Registration;

use App\Mail\BaseMailable;
use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class Reviewable extends BaseMailable
{
    use Queueable, SerializesModels;

    public $community;
    public $user;

    public function __construct(User $user, Community $community)
    {
        $this->user = $user;
        $this->community = $community;
    }

    public function build()
    {
        $subject =
            "LocoMotion - " .
            $this->user->full_name .
            " s'est inscrit dans " .
            $this->community->name;
        return $this->view("emails.registration.reviewable")
            ->subject($subject)
            ->text("emails.registration.reviewable_text")
            ->with([
                "title" => $subject,
            ]);
    }
}
