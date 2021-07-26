<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Loan;
use App\Models\Handover;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class HandoverContested extends BaseMailable
{
    use Queueable, SerializesModels;

    public $handover;
    public $loan;
    public $receiver;
    public $caller;

    public function __construct(
        Handover $handover,
        Loan $loan,
        User $receiver,
        User $caller
    ) {
        $this->handover = $handover;
        $this->loan = $loan;
        $this->receiver = $receiver;
        $this->caller = $caller;
    }

    public function build()
    {
        return $this->view("emails.loan.handover_contested")
            ->subject("LocoMotion - Données de retour du véhicule contestées")
            ->text("emails.loan.handover_contested_text")
            ->with([
                "title" => "Données de retour du véhicule contestées",
            ]);
    }
}
