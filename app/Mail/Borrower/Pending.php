<?php

namespace App\Mail\Borrower;

use App\Mail\BaseMailable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class Pending extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;
    public $isRegistrationSubmitted;

    public function __construct(User $user, $isRegistrationSubmitted)
    {
        $this->user = $user;
        $this->isRegistrationSubmitted = $isRegistrationSubmitted;
    }

    public function build()
    {
        return $this->view("emails.borrower.pending")
            ->subject("Coloc'Auto - Votre dossier de conduite est approuvé!")
            ->text("emails.borrower.pending_text")
            ->with([
                "title" => "Votre dossier de conduite est approuvé!",
            ]);
    }
}
