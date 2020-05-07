<?php

namespace App\Mail;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Owner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $borrower;
    public $loan;
    public $owner;

    public function __construct(Borrower $borrower, Owner $owner, Loan $loan) {
         $this->borrower = $borrower;
         $this->owner = $owner;
         $this->loan = $loan;
    }

    public function build() {
        return $this->view('emails.loan.created')
            ->subject('LocoMotion - Nouvel emprunt')
            ->text('emails.loan.created_text')
            ->with([
                'title' => 'Nouvel emprunt',
            ]);
    }
}
