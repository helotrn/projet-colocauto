<?php

namespace App\Mail\Invitation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatedForAutonomy extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view("emails.invitation.created_for_autonomy")
            ->subject("Coloc'Auto - Invitation à créer un compte")
            ->text("emails.invitation.created_for_autonomy_text")
            ->with([
                "title" => "Invitation à créer un compte",
            ]);
    }
}
