<?php

namespace App\Mail\Registration;

use App\Mail\MandrillMailable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class Approved extends MandrillMailable
{
    use Queueable, SerializesModels;

    public $user;
    public $template = "base";

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->templateVars = [
            "name" => $user->name,
            "full_name" => $user->full_name,
            "last_name" => $user->last_name,
        ];
    }

    public function build()
    {
        return $this->subject("Bienvenue dans LocoMotion, c'est parti!");
    }
}
