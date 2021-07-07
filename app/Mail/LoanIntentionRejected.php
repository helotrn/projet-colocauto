<?php

namespace App\Mail;

use App\Models\Borrower;
use App\Models\Intention;
use App\Models\Loan;
use App\Models\Owner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class LoanIntentionRejected extends BaseMailable
{
    use Queueable, SerializesModels;

    public $borrower;
    public $intention;
    public $loan;
    public $owner;

    public function __construct(
        Intention $intention,
        Loan $loan,
        Borrower $borrower,
        Owner $owner
    ) {
         $this->intention = $intention;
         $this->loan = $loan;
         $this->borrower = $borrower;
         $this->owner = $owner;
    }

    public function build() {
        return $this->view('emails.loan.intention_rejected')
            ->subject('LocoMotion - Emprunt refusé')
            ->text('emails.loan.intention_rejected_text')
            ->with([
                'title' => 'Emprunt refusé',
            ]);
    }
}
