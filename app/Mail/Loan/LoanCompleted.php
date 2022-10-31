<?php

namespace App\Mail\Loan;

use App\Mail\MandrillMailable;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class LoanCompleted extends MandrillMailable
{
    use Queueable, SerializesModels;

    public $template = "loan-completed";

    public function __construct(User $user, Loan $loan, bool $isOwner)
    {
        $this->subject = "Emprunt Complété!";
        $this->templateVars = [
            "FNAME" => $user->name,
            "email" => $user->email,
            "isOwner" => $isOwner,
            "loanableName" => $loan->loanable->name,
            "borrowerName" => $loan->borrower->user->name,
            "duration" => self::formatDuration(
                $loan->actual_duration_in_minutes
            ),
            "departureAt" => (new Carbon(
                $loan->departure_at
            ))->toDateTimeString(),
            "loanableType" => self::formatLoanableType($loan->loanable->type),
        ];
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

    private static function formatLoanableType($type)
    {
        switch ($type) {
            case "car":
                return "Auto";
                break;

            case "bike":
                return "Vélo";
                break;

            case "trailer":
                return "Remorque";
                break;

            default:
                return "";
                break;
        }
    }
}
