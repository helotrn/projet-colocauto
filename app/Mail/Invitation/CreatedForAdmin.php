<?php

namespace App\Mail\Invitation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Community;

class CreatedForAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $community;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, Community $community=null, $token)
    {
        $this->email = $email;
        $this->community = $community;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view("emails.invitation.created_for_admin")
            ->subject("Coloc'Auto - Invitation à administrer une communauté")
            ->text("emails.invitation.created_for_admin_text")
            ->with([
                "title" => "Invitation à administrer une communauté",
            ]);
    }
}
