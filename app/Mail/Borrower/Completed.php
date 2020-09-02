<?php

namespace App\Mail\Borrower;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Completed extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user) {
         $this->user = $user;
    }

    public function build() {
        return $this->view('emails.borrower.completed')
            ->subject('Dossier de conduite complété')
            ->text('emails.borrower.completed_text')
            ->with([
                'title' => 'Dossier de conduite complété',
            ]);
    }
}
