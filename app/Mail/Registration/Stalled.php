<?php

namespace App\Mail\Registration;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Stalled extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user) {
         $this->user = $user;
    }

    public function build() {
        return $this->view('emails.registration.stalled')
            ->subject('LocoMotion - Suivi de votre inscription')
            ->text('emails.registration.stalled_text')
            ->with([
                'title' => 'LocoMotion - Suivi de votre inscription',
            ]);
    }
}
