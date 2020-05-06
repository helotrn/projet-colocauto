<?php

namespace App\Mail;

use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationReviewable extends Mailable
{
    use Queueable, SerializesModels;

    public $community;
    public $user;

    public function __construct(User $user, Community $community) {
         $this->user = $user;
         $this->community = $community;
    }

    public function build() {
        return $this->view('emails.registration.reviewable')
            ->subject('LocoMotion - Nouveau membre inscrit dans ' . $this->community->name)
            ->text('emails.registration.reviewable_text')
            ->with([
                'title' => 'Nouveau membre inscrit dans ' . $this->community->name,
            ]);
    }
}
