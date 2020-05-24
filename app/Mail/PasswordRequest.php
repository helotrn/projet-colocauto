<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;

    public function __construct(User $user, $token) {
         $this->user = $user;
         $this->token = $token;
    }

    public function build() {
        return $this->view('emails.password.request')
            ->subject('LocoMotion - Réinitialisation du mot de passe')
            ->text('emails.registration.submitted_text')
            ->with([
                'title' => 'Réinitialisation de mot de passe',
                'expiration' => floor(config(
                    'auth.passwords.' . config('auth.defaults.passwords') . '.expire'
                ) / 60),
                'route' => url(
                    route(
                        'password.reset',
                        [
                            'token' => $this->token,
                            'email' => $this->user->email,
                        ]
                    )
                ),
            ]);
    }
}
