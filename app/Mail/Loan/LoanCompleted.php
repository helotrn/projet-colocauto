<?php

namespace App\Mail\Loan;

use App\Mail\BaseMailable;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class LoanCompleted extends BaseMailable
{
    use Queueable, SerializesModels;

    public $loan;
    public $user;
    public $isOwner;
    public $duration;
    public $departureAt;

    public function __construct(User $user, Loan $loan, bool $isOwner)
    {
        $this->loan = $loan;
        $this->user = $user;
        $this->isOwner = $isOwner;
        $this->duration = self::formatDuration(
            $loan->actual_duration_in_minutes
        );
    }

    public function build()
    {
        return $this->view("emails.loan.completed")
            ->subject("Coloc'Auto - Emprunt complété !")
            ->text("emails.loan.completed_text")
            ->with([
                "title" => "Emprunt complété !",
            ]);
    }

    private static function formatDuration($duration)
    {
        $hours = intval($duration / 60.0);
        if ($hours < 2) {
            return "$duration minutes";
        }
        $minutes = $duration - $hours * 60;
        return "${hours}h ${minutes}m";
    }
}
