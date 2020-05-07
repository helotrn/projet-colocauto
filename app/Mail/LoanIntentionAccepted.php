<?php

namespace App\Mail;

use App\Models\Borrower;
use App\Models\Intention;
use App\Models\Loan;
use App\Models\Owner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanIntentionAccepted extends Mailable
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
        return $this->view('emails.loan.intention_accepted')
            ->subject('LocoMotion - Emprunt accepté')
            ->text('emails.loan.intention_accepted_text')
            ->with([
                'title' => 'Emprunt accepté',
            ]);
    }
}
