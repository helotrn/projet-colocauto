<?php

namespace App\Mail\Loan;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PrePaymentMissing extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loan;

    public function __construct(User $user, Loan $loan) {
         $this->user = $user;
         $this->loan = $loan;
    }

    public function build() {
        return $this->view('emails.loan.pre_payment_missing')
            ->subject(
                "LocoMotion - Merci de faire le pré-paiement avant d'emprunter la "
                . "voiture de votre voisin-e"
            )
            ->text('emails.loan.pre_payment_missing_text')
            ->with([
                'title' => "Merci de faire le pré-paiement avant d'emprunter la "
                . "voiture de votre voisin-e"
            ]);
    }
}
