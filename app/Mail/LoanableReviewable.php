<?php

namespace App\Mail;

use App\Models\Community;
use App\Models\Loanable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanableReviewable extends Mailable
{
    use Queueable, SerializesModels;

    public $community;
    public $loanable;
    public $user;

    public function __construct(User $user, Community $community, Loanable $loanable) {
         $this->user = $user;
         $this->community = $community;
         $this->loanable = $loanable;
    }

    public function build() {
        return $this->view('emails.loanable.reviewable')
            ->subject('LocoMotion - Nouveau véhicule ajouté dans ' . $this->community->name)
            ->text('emails.loanable.reviewable_text')
            ->with([
                'title' => 'Nouveau véhicule ajouté dans ' . $this->community->name,
            ]);
    }
}
