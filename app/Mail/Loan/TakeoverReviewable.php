<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Loan;
use App\Models\Takeover;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class TakeoverReviewable extends BaseMailable
{
    use Queueable, SerializesModels;

    public $takeover;
    public $loan;
    public $caller;

    public function __construct(Takeover $takeover, Loan $loan, User $caller)
    {
        $this->takeover = $takeover;
        $this->loan = $loan;
        $this->caller = $caller;
    }

    public function build()
    {
        return $this->view("emails.loan.takeover_reviewable")
            ->subject("Coloc'Auto - Contestation d'une prise de possession")
            ->text("emails.loan.takeover_reviewable_text")
            ->with([
                "title" => "Contestation d'une prise de possession",
            ]);
    }
}
