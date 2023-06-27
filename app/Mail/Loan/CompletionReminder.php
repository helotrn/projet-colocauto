<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CompletionReminder extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loan;

    public function __construct(User $user, Loan $loan)
    {
        $this->user = $user;
        $this->loan = $loan;
    }

    public function build()
    {
        return $this->view("emails.loan.completion_reminder")
            ->subject("Coloc'Auto - Veuillez compléter votre emprunt")
            ->text("emails.loan.completion_reminder_text")
            ->with([
                "title" => "Veuillez compléter votre emprunt",
            ]);
    }
}
