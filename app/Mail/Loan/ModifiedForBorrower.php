<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ModifiedForBorrower extends BaseMailable
{
    use Queueable, SerializesModels;

    public $borrower;
    public $loan;
    public $owner;
    public $user;

    public function __construct(Borrower $borrower, Owner $owner, Loan $loan, User $user)
    {
        $this->borrower = $borrower;
        $this->owner = $owner;
        $this->loan = $loan;
        $this->user = $user;
    }

    public function build()
    {
        return $this->view("emails.loan.modified_for_borrower")
            ->subject("Coloc'Auto - Emprunt modifié")
            ->text("emails.loan.modified_for_borrower_text")
            ->with([
                "title" => "Emprunt modifié",
            ]);
    }
}
