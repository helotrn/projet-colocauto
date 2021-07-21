<?php

namespace App\Mail;

use App\Models\Loanable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class LoanableCreated extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loanable;

    public function __construct(User $user, Loanable $loanable)
    {
        $this->user = $user;
        $this->loanable = $loanable;
    }

    public function build()
    {
        return $this->view("emails.loanable.created")
            ->subject("LocoMotion - Véhicule ajouté")
            ->text("emails.loanable.created_text")
            ->with([
                "title" => "Véhicule ajouté",
            ]);
    }
}
