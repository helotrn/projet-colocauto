<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class LoanIncidentResolved extends BaseMailable
{
    use Queueable, SerializesModels;

    public $incident;
    public $loan;
    public $target;

    public function __construct(Incident $incident, Loan $loan, $target)
    {
        $this->incident = $incident;
        $this->loan = $loan;
        $this->target = $target;
    }

    public function build()
    {
        return $this->view("emails.loan.incident_resolved")
            ->subject("LocoMotion - Incident résolu")
            ->text("emails.loan.incident_resolved_text")
            ->with([
                "title" => "Incident résolu",
            ]);
    }
}
