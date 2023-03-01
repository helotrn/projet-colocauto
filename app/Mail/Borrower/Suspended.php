<?php

namespace App\Mail\Borrower;

use App\Mail\BaseMailable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class Suspended extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view("emails.borrower.suspended")
            ->subject(
                "Coloc'Auto - Votre compte a été suspendu"
            )
            ->text("emails.borrower.suspended_text")
            ->with([
                "title" =>
                    "Votre compte a été suspendu",
            ]);
    }
}
