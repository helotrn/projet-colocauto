<?php

namespace App\Mail\Borrower;

use App\Mail\BaseMailable;
use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class Reviewable extends BaseMailable
{
    use Queueable, SerializesModels;

    public $communities;
    public $user;

    /*
      @communities
        Array of 0, 1 or multiple communities.
        Accepts single Community object
        Accepts NULL to be more robust.
     */
    public function __construct(User $user, $communities = null)
    {
        $this->user = $user;

        if (!$communities) {
            $communities = collect();
        } else {
            $communities = collect([$communities])->flatten();
        }

        $this->communities = $communities;
    }

    public function build()
    {
        $communitiesCount = count($this->communities);
        if ($communitiesCount > 1) {
            $subject = "Profil d'emprunteur complété dans $communitiesCount communautés";
        } elseif (1 === $communitiesCount) {
            $communityName = $this->communities->first()->name;
            $subject = "Profil d'emprunteur complété dans {$communityName}";
        } else {
            $subject = "Profil d'emprunteur complété";
        }

        return $this->view("emails.borrower.reviewable")
            ->subject($subject)
            ->text("emails.borrower.reviewable_text")
            ->with([
                "title" => $subject,
            ]);
    }
}
