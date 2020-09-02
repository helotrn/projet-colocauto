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

    public $community;
    public $user;

    public function __construct(User $user, Community $community) {
         $this->user = $user;
         $this->community = $community;
    }

    public function build() {
        return $this->view('emails.borrower.reviewable')
            ->subject("Profil d'emprunteur complété dans " . $this->community->name)
            ->text('emails.borrower.reviewable_text')
            ->with([
                'title' => "Profil d'emprunteur complété dans " . $this->community->name,
            ]);
    }
}
