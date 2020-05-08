<?php

namespace App\Mail;

use App\Models\Borrower;
use App\Models\Incident;
use App\Models\Loan;
use App\Models\Owner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanIncidentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $borrower;
    public $incident;
    public $loan;
    public $owner;

    public function __construct(
        Incident $incident,
        Loan $loan,
        Borrower $borrower,
        Owner $owner
    ) {
         $this->borrower = $borrower;
         $this->incident = $incident;
         $this->loan = $loan;
         $this->owner = $owner;
    }

    public function build() {
        return $this->view('emails.loan.incident_created')
            ->subject("LocoMotion - Rapport d'incident")
            ->text('emails.loan.incident_created_text')
            ->with([
                'title' => "Rapport d'incident",
            ]);
    }
}
