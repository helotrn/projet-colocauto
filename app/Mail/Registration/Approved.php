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
    public $template = "confirmation-d-inscription-sp-13-au-27-oct";

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->subject = "Bienvenue dans LocoMotion, c'est parti!";
        $this->templateVars = [
            "FNAME" => $user->name,
            "NEIGHBOURHOOD" => $user->communities->first()->name,
            "full_name" => $user->full_name,
            "last_name" => $user->last_name,
            "title" => "Bienvenue dans LocoMotion, c'est parti!",
        ];
    }
}