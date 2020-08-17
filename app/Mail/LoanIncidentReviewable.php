<?php

namespace App\Mail;

use App\Models\Borrower;
use App\Models\Community;
use App\Models\Incident;
use App\Models\Loan;
use App\Models\Owner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanIncidentReviewable extends Mailable
{
    use Queueable, SerializesModels;

    public $borrower;
    public $incident;
    public $loan;
    public $owner;
    public $community;

    public function __construct(
        Incident $incident,
        Loan $loan,
        Borrower $borrower,
        ?Owner $owner,
        Community $community
    ) {
         $this->borrower = $borrower;
         $this->incident = $incident;
         $this->loan = $loan;
         $this->owner = $owner;
         $this->community = $community;
    }

    public function build() {
        return $this->view('emails.loan.incident_reviewable')
            ->subject("LocoMotion - Rapport d'incident")
            ->text('emails.loan.incident_reviewable_text')
            ->with([
                'title' => "Rapport d'incident",
            ]);
    }
}
