<?php

namespace App\Mail\Borrower;

use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Reviewable extends Mailable
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
    public function __construct(User $user, $communities = null) {
        $this->user = $user;

        if (null == $communities) {
            $communities = [];
        } elseif (!is_array($communities)) {
            $communities = [$communities];
        }

        $this->communities = $communities;
    }

    public function build() {
        $n_communities = count($this->communities);
        if ($n_communities > 1) {
            $subject = "Profil d'emprunteur complété dans ".$n_communities." communautés";
        } elseif (1 == $n_communities) {
                             // Loop to accept any type of key.
            foreach ($this->communities as $community) {
                $subject = "Profil d'emprunteur complété dans ".$community->name;
            }
        } else {
            $subject = "Profil d'emprunteur complété";
        }


        return $this->view('emails.borrower.reviewable')
            ->subject($subject)
            ->text('emails.borrower.reviewable_text')
            ->with([
                'title' => $subject,
            ]);
    }
}
