<?php

namespace App\Mail\Borrower;

use App\Mail\BaseMailable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class Approved extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user) {
         $this->user = $user;
    }

    public function build() {
        return $this->view('emails.borrower.approved')
            ->subject('LocoMotion - Empruntez dès maintenant la voiture de vos voisin-e-s!')
            ->text('emails.borrower.approved_text')
            ->with([
                'title' => 'Empruntez dès maintenant la voiture de vos voisin-e-s!',
            ]);
    }
}
