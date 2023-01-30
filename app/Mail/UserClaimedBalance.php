<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class UserClaimedBalance extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view("emails.user.claimed_balance")
            ->subject("Coloc'Auto - Montant réclamé")
            ->text("emails.user.claimed_balance_text")
            ->with([
                "title" => "Montant réclamé",
            ]);
    }
}
