<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Loan;
use App\Models\Takeover;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TakeoverContestationResolved extends BaseMailable
{
    use Queueable, SerializesModels;

    public $takeover;
    public $loan;
    public $receiver;
    public $admin;

    public function __construct(
        Takeover $takeover,
        Loan $loan,
        User $receiver,
        User $admin
    ) {
         $this->takeover = $takeover;
         $this->loan = $loan;
         $this->receiver = $receiver;
         $this->admin = $admin;
    }

    public function build() {
        return $this->view('emails.loan.takeover_contestation_resolved')
            ->subject("LocoMotion - Données de l'emprunt mises à jour")
            ->text('emails.loan.takeover_contestation_resolved_text')
            ->with([
                'title' => "Données de l'emprunt mises à jour"
            ]);
    }
}
