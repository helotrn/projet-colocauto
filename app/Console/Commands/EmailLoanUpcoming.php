<?php

namespace App\Console\Commands;

use App\Mail\Loan\Upcoming as LoanUpcoming;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;
use Log;

class EmailLoanUpcoming extends Command
{
    protected $signature = 'email:loan:upcoming
                            {--pretend : Do not send emails}';

    protected $description = "Send loan upcoming emails (in three hours)";

    private $pretend = false;

    public function handle()
    {
        if ($this->option("pretend")) {
            $this->pretend = true;
        }

        Log::info(
            "Fetching loans starting in three hours or less created at least three hours before now..."
        );

        $query = $this->getQuery();

        $loans = $query->cursor();
        foreach ($loans as $loan) {
            $user = $loan->borrower->user;
            if (!$this->pretend) {
                Log::info(
                    "Sending LoanUpcoming email to borrower at: $user->email"
                );

                Mail::to(
                    $user->email,
                    $user->name . " " . $user->last_name
                )->send(new LoanUpcoming($user, $loan));

                // Loanable has an owner and is not self service.
                if (
                    $loan->loanable->owner &&
                    !$loan->loanable->is_self_service
                ) {
                    $ownerUser = $loan->loanable->owner->user;

                    Log::info(
                        "Sending LoanUpcoming email to owner at: $ownerUser->email"
                    );

                    Mail::to(
                        $ownerUser->email,
                        $ownerUser->name . " " . $ownerUser->last_name
                    )->send(new LoanUpcoming($user, $loan));
                }

                $meta = $loan->meta;
                $meta["sent_loan_upcoming_email"] = true;
                $loan->meta = $meta;

                $loan->save();
            } else {
                Log::info(
                    "Would have sent LoanUpcoming email to borrower at: {$user->email}" .
                        " for loan with id: {$loan->id}"
                );

                // Loanable has an owner and is not self service.
                if (
                    $loan->loanable->owner &&
                    !$loan->loanable->is_self_service
                ) {
                    $ownerUser = $loan->loanable->owner->user;
                    Log::info(
                        "Would have sent LoanUpcoming email to owner at: {$ownerUser->email} " .
                            "for loan with id: {$loan->id}"
                    );
                }
            }
        }

        Log::info("Done.");
    }

    public static function getQuery()
    {
        $now = Carbon::now();
        $threeHoursAgo = $now->copy()->subtract(3, "hours");
        $inThreeHours = $now->copy()->add(3, "hours");

        $query = Loan::where("departure_at", "<=", $inThreeHours)
            ->where("departure_at", ">", $now)
            ->where("loans.created_at", "<=", $threeHoursAgo)
            ->where("meta->sent_loan_upcoming_email", null);

        $columnDefinitions = Loan::getColumnsDefinition();
        $query = $columnDefinitions["loan_status"]($query);
        $query = $columnDefinitions["*"]($query);

        $query->where($columnDefinitions["loan_status"](), "=", "in_process");

        return $query;
    }
}
