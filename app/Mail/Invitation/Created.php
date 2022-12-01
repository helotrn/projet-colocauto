<?php

namespace App\Mail\Invitation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Community;

class Created extends Mailable
{
    use Queueable, SerializesModels;

    public $community;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Community $community, $token)
    {
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
        return $this->view("emails.invitation.created")
            ->subject("Coloc'Auto - Invitation à rejoindre une communauté")
            ->text("emails.invitation.created_text")
            ->with([
                "title" => "Invitation à rejoindre une communauté",
            ]);
    }
}
