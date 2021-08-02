<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Loan;
use App\Models\Takeover;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TakeoverContested extends BaseMailable
{
    use Queueable, SerializesModels;

    public $takeover;
    public $loan;
    public $receiver;
    public $caller;

    public function __construct(
        Takeover $takeover,
        Loan $loan,
        User $receiver,
        User $caller
    ) {
        $this->takeover = $takeover;
        $this->loan = $loan;
        $this->receiver = $receiver;
        $this->caller = $caller;
    }

    public function build()
    {
        return $this->view("emails.loan.takeover_contested")
            ->subject("LocoMotion - Données de prise de possession contestées")
            ->text("emails.loan.takeover_contested_text")
            ->with([
                "title" => "Données de prise de possession contestées",
            ]);
    }
}
