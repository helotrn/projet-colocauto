<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoicePaid extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $invoice;

    public function __construct(User $user, $invoice) {
         $this->user = $user;
         $this->invoice = $invoice;
    }

    public function build() {
        return $this->view('emails.invoice.paid')
            ->subject('LocoMotion - Facture payée')
            ->text('emails.invoice.paid_text')
            ->with([
                'title' => 'Facture payée',
            ]);
    }
}
