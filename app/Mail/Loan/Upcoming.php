<?php

namespace App\Mail\Loan;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Upcoming extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loan;

    public function __construct(User $user, Loan $loan) {
         $this->user = $user;
         $this->loan = $loan;
    }

    public function build() {
        return $this->view('emails.loan.upcoming')
            ->subject('LocoMotion - Votre réservation commence dans 3 heures!')
            ->text('emails.loan.upcoming_text')
            ->with([
                'title' => 'Votre réservation commence dans 3 heures!'
            ]);
    }
}
