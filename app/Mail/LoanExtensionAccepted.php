<?php

namespace App\Mail;

use App\Models\Borrower;
use App\Models\Extension;
use App\Models\Loan;
use App\Models\Owner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class LoanExtensionAccepted extends BaseMailable
{
    use Queueable, SerializesModels;

    public $borrower;
    public $extension;
    public $loan;
    public $owner;

    public function __construct(
        Extension $extension,
        Loan $loan,
        Borrower $borrower,
        Owner $owner
    ) {
         $this->borrower = $borrower;
         $this->extension = $extension;
         $this->loan = $loan;
         $this->owner = $owner;
    }

    public function build() {
        return $this->view('emails.loan.extension_accepted')
            ->subject("LocoMotion - Demande d'extension")
            ->text('emails.loan.extension_accepted_text')
            ->with([
                'title' => "Demande d'extension acceptée",
            ]);
    }
}
