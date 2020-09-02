<?php

namespace App\Mail\Registration;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Approved extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user) {
         $this->user = $user;
    }

    public function build() {
        return $this->view('emails.registration.approved')
            ->subject("Bienvenue dans LocoMotion, c'est parti!")
            ->text('emails.registration.approved_text')
            ->with([
                'title' => "Bienvenue dans LocoMotion, c'est parti!",
            ]);
    }
}
