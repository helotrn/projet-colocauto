<?php

namespace App\Mail\Registration;

use App\Mail\BaseMailable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class Stalled extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view("emails.registration.stalled")
            ->subject("Coloc'Auto - Suivi de votre inscription")
            ->text("emails.registration.stalled_text")
            ->with([
                "title" => "Coloc'Auto - Suivi de votre inscription",
            ]);
    }
}
