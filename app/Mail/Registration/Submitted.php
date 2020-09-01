<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Submitted extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user) {
         $this->user = $user;
    }

    public function build() {
        return $this->view('emails.registration.submitted')
            ->subject('Bienvenue dans LocoMotion! Ça y est presque!')
            ->text('emails.registration.submitted_text')
            ->with([
                'title' => 'Bienvenue dans LocoMotion!',
            ]);
    }
}
