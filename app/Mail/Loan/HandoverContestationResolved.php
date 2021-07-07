<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Loan;
use App\Models\Handover;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class HandoverContestationResolved extends BaseMailable
{
    use Queueable, SerializesModels;

    public $handover;
    public $loan;
    public $receiver;
    public $admin;

    public function __construct(
        Handover $handover,
        Loan $loan,
        User $receiver,
        User $admin
    ) {
         $this->handover = $handover;
         $this->loan = $loan;
         $this->receiver = $receiver;
         $this->admin = $admin;
    }

    public function build() {
        return $this->view('emails.loan.handover_contestation_resolved')
            ->subject("LocoMotion - Données de l'emprunt mises à jour")
            ->text('emails.loan.handover_contestation_resolved_text')
            ->with([
                'title' => "Données de l'emprunt mises à jour"
            ]);
    }
}
