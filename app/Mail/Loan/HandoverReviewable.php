<?php

namespace App\Mail\Loan;

use App\Models\Loan;
use App\Models\Handover;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HandoverReviewable extends Mailable
{
    use Queueable, SerializesModels;

    public $handover;
    public $loan;
    public $caller;

    public function __construct(
        Handover $handover,
        Loan $loan,
        User $caller
    ) {
         $this->handover = $handover;
         $this->loan = $loan;
         $this->caller = $caller;
    }

    public function build() {
        return $this->view('emails.loan.handover_reviewable')
            ->subject('LocoMotion - Contestation du retour du véhicule')
            ->text('emails.loan.handover_reviewable_text')
            ->with([
                'title' => 'Contestation du retour du véhicule',
            ]);
    }
}
