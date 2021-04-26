<?php

namespace App\Mail\Loan;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Canceled extends Mailable
{
    use Queueable, SerializesModels;

    public $loan;
    public $sender;
    public $receiver;

    public function __construct(User $sender, User $receiver, Loan $loan) {
         $this->loan = $loan;
         $this->receiver = $receiver;
         $this->sender = $sender;
    }

    public function build() {
        return $this->view('emails.loan.canceled')
            ->subject('LocoMotion - Emprunt annulé')
            ->text('emails.loan.canceled_text')
            ->with([
                'title' => 'Emprunt annulé',
            ]);
    }
}
