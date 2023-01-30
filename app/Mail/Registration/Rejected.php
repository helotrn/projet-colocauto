<?php

namespace App\Mail\Registration;

use App\Mail\BaseMailable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class Rejected extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view("emails.registration.rejected")
            ->subject("Coloc'Auto - Votre inscription a été refusée")
            ->text("emails.registration.rejected_text")
            ->with([
                "title" => "Votre inscription a été refusée",
            ]);
    }
}
